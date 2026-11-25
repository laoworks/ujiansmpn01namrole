<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Models\Soal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SoalController extends Controller
{
    /**
     * Disk penyimpanan gambar soal. Sengaja "local" (private): gambar
     * ditayangkan lewat route media.soal, bukan lewat public/storage.
     */
    private const DISK = 'local';

    public function index(Request $request): View
    {
        $soal = Soal::with('mapel')
            ->where('guru_id', $request->user()->id)
            ->when(
                $request->mapel_id,
                fn ($query, $id) => $query->where('mapel_id', $id)
            )
            ->when(
                $request->tipe,
                fn ($query, $tipe) => $query->where('tipe', $tipe)
            )
            ->when(
                $request->q,
                fn ($query, $kata) => $query->where(
                    'pertanyaan',
                    'like',
                    "%{$kata}%"
                )
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('guru.soal.index', [
            'soal' => $soal,
            'mapel' => Mapel::orderBy('nama')->get(),
            'tipeLabel' => Soal::TIPE_LABEL,
        ]);
    }

    public function create(): View
    {
        return view('guru.soal.form', [
            'soal' => new Soal(),
            'mapel' => Mapel::orderBy('nama')->get(),
            'tipeLabel' => Soal::TIPE_LABEL,
            'aksi' => route('guru.soal.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$data, $opsi] = $this->validasi($request);

        $fileBaru = [];

        try {
            [$data, $opsi, $fileBaru] = $this->simpanGambar(
                $data,
                $opsi
            );

            DB::transaction(function () use (
                $data,
                $opsi,
                $request
            ) {
                $soal = Soal::create(
                    $data + ['guru_id' => $request->user()->id]
                );

                $soal->opsi()->createMany($opsi);
            });
        } catch (Throwable $e) {
            $this->hapusFile($fileBaru);
            throw $e;
        }

        return redirect()
            ->route('guru.soal.index')
            ->with('status', 'Soal berhasil ditambahkan.');
    }

    public function show(Soal $soal): View
    {
        $this->milik($soal);

        return view('guru.soal.show', [
            'soal' => $soal->load('mapel', 'opsi'),
            'tipeLabel' => Soal::TIPE_LABEL,
        ]);
    }

    public function edit(Soal $soal): View|RedirectResponse
    {
        $this->milik($soal);

        if ($this->terkunci($soal)) {
            return redirect()->route('guru.soal.index')->withErrors([
                'hapus' => 'Soal ini sudah dikerjakan siswa dalam ujian, jadi tidak bisa diubah.',
            ]);
        }

        return view('guru.soal.form', [
            'soal' => $soal->load('opsi'),
            'mapel' => Mapel::orderBy('nama')->get(),
            'tipeLabel' => Soal::TIPE_LABEL,
            'aksi' => route('guru.soal.update', $soal),
        ]);
    }

    public function update(Request $request, Soal $soal): RedirectResponse
    {
        $this->milik($soal);

        if ($this->terkunci($soal)) {
            return redirect()->route('guru.soal.index')->withErrors([
                'hapus' => 'Soal ini sudah dikerjakan siswa dalam ujian, jadi tidak bisa diubah.',
            ]);
        }

        $gambarLama = $this->semuaGambar($soal);

        [$data, $opsi] = $this->validasi($request, $soal);

        $fileBaru = [];

        try {
            [$data, $opsi, $fileBaru] = $this->simpanGambar($data, $opsi);

            DB::transaction(function () use ($soal, $data, $opsi) {
                $soal->update($data);
                $soal->opsi()->delete();
                $soal->opsi()->createMany($opsi);
            });
        } catch (Throwable $e) {
            // Gagal menyimpan: buang file yang baru saja diunggah
            $this->hapusFile($fileBaru);
            throw $e;
        }

        // Hapus file yang sudah tidak dipakai lagi
        $gambarBaru = array_filter(array_merge(
            [$data['gambar']],
            array_column($opsi, 'gambar')
        ));

        $this->hapusFile(array_diff($gambarLama, $gambarBaru));

        return redirect()
            ->route('guru.soal.index')
            ->with('status', 'Soal berhasil diperbarui.');
    }

    public function destroy(Soal $soal): RedirectResponse
    {
        $this->milik($soal);

        if (DB::table('ujian_soal')->where('soal_id', $soal->id)->exists()) {
            return back()->withErrors([
                'hapus' => 'Soal ini dipakai di ujian dan tidak bisa dihapus. Lepaskan dari ujian terlebih dahulu.',
            ]);
        }

        $gambar = $this->semuaGambar($soal);

        $soal->delete();
        $this->hapusFile($gambar);

        return back()->with('status', 'Soal berhasil dihapus.');
    }

    private function milik(Soal $soal): void
    {
        abort_unless(
            (int) $soal->guru_id === (int) auth()->id(),
            403
        );
    }

    private function semuaGambar(Soal $soal): array
    {
        return array_values(array_filter(array_merge(
            [$soal->gambar],
            $soal->opsi()->pluck('gambar')->all()
        )));
    }

    /**
     * Simpan gambar baru dan kembalikan path yang disimpan.
     *
     * @return array{0: array, 1: array, 2: array} [$data, $opsi, $fileBaru]
     */
    private function simpanGambar(
        array $data,
        array $opsi
    ): array {
        $fileBaru = [];

        try {
            $fileSoal = $data['_file'] ?? null;
            $gambarLamaSoal = $data['_lama'] ?? null;

            if ($fileSoal) {
                $path = $fileSoal->store('soal', self::DISK);
                $fileBaru[] = $path;
                $data['gambar'] = $path;
            } else {
                $data['gambar'] = $gambarLamaSoal;
            }

            unset($data['_file'], $data['_lama']);

            foreach ($opsi as $i => $item) {
                $fileOpsi = $item['_file'] ?? null;
                $gambarLamaOpsi = $item['_lama'] ?? null;

                if ($fileOpsi) {
                    $path = $fileOpsi->store('soal', self::DISK);
                    $fileBaru[] = $path;
                    $opsi[$i]['gambar'] = $path;
                } else {
                    $opsi[$i]['gambar'] = $gambarLamaOpsi;
                }

                unset(
                    $opsi[$i]['_file'],
                    $opsi[$i]['_lama']
                );
            }

            return [$data, $opsi, $fileBaru];
        } catch (Throwable $e) {
            $this->hapusFile($fileBaru);
            throw $e;
        }
    }

    private function hapusFile(array $paths): void
    {
        $paths = array_values(array_unique(array_filter($paths)));

        if ($paths !== []) {
            Storage::disk(self::DISK)->delete($paths);
        }
    }

    private function validasi(
        Request $request,
        ?Soal $soal = null
    ): array {
        $aturanGambar = [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:1024',
        ];

        $v = $request->validate([
            'mapel_id' => [
                'required',
                'exists:mapel,id',
            ],

            'tipe' => [
                'required',
                Rule::in(array_keys(Soal::TIPE_LABEL)),
            ],

            'stimulus' => [
                'nullable',
                'string',
                'required_if:tipe,benar_salah',
            ],

            'gambar' => $aturanGambar,
            'gambar_lama' => ['nullable', 'string'],

            'pertanyaan' => ['required', 'string'],

            'opsi' => ['required', 'array', 'min:2', 'max:6'],
            'opsi.*.teks' => ['nullable', 'string', 'max:1000'],
            'opsi.*.gambar' => $aturanGambar,
            'opsi.*.gambar_lama' => ['nullable', 'string'],
            'opsi.*.bs' => ['nullable', 'in:0,1'],

            'benar_pg' => [
                'nullable',
                'required_if:tipe,pg',
                'integer',
            ],

            'benar_kompleks' => [
                'nullable',
                'required_if:tipe,pg_kompleks',
                'array',
                'size:2',
            ],

            'benar_kompleks.*' => ['integer'],
        ], [
            'stimulus.required_if' =>
                'Stimulus wajib diisi untuk soal benar/salah.',

            'benar_pg.required_if' =>
                'Pilih satu kunci jawaban.',

            'benar_kompleks.required_if' =>
                'Pilih dua jawaban yang benar.',

            'benar_kompleks.size' =>
                'Pilih tepat dua jawaban yang benar.',

            'gambar.image' =>
                'Gambar soal harus berupa gambar yang valid.',

            'gambar.mimes' =>
                'Format gambar soal harus JPG, JPEG, PNG, atau WEBP.',

            'gambar.max' =>
                'Ukuran gambar soal maksimal 1 MB.',

            'opsi.*.gambar.image' =>
                'File gambar opsi harus berupa gambar yang valid.',

            'opsi.*.gambar.mimes' =>
                'Format gambar opsi harus JPG, JPEG, PNG, atau WEBP.',

            'opsi.*.gambar.max' =>
                'Ukuran gambar opsi maksimal 1 MB.',
        ]);

        $tipe = $v['tipe'];
        $opsiInput = array_values($v['opsi']);
        $jumlah = count($opsiInput);

        if ($tipe !== 'benar_salah' && $jumlah !== 4) {
            throw ValidationException::withMessages([
                'opsi' =>
                    'Soal pilihan ganda harus memiliki tepat 4 opsi (A-D).',
            ]);
        }

        $benar = [];

        if ($tipe === 'pg') {
            $kunci = (int) $v['benar_pg'];

            if ($kunci < 0 || $kunci >= $jumlah) {
                throw ValidationException::withMessages([
                    'benar_pg' => 'Kunci jawaban tidak valid.',
                ]);
            }

            $benar = [$kunci];
        } elseif ($tipe === 'pg_kompleks') {
            $benar = array_values(array_unique(array_map(
                'intval',
                $v['benar_kompleks']
            )));

            if (
                count($benar) !== 2 ||
                min($benar) < 0 ||
                max($benar) >= $jumlah
            ) {
                throw ValidationException::withMessages([
                    'benar_kompleks' =>
                        'Pilih tepat dua jawaban yang benar.',
                ]);
            }
        } else {
            foreach ($opsiInput as $o) {
                if (!isset($o['bs'])) {
                    throw ValidationException::withMessages([
                        'opsi' =>
                            'Tentukan benar atau salah untuk setiap pernyataan.',
                    ]);
                }
            }
        }

        $gambarSoalLama = null;

        if (
            $soal &&
            filled($v['gambar_lama'] ?? null) &&
            $v['gambar_lama'] === $soal->gambar
        ) {
            $gambarSoalLama = $soal->gambar;
        }

        $gambarOpsiDiizinkan = $soal
            ? $soal->opsi()->pluck('gambar')->filter()->all()
            : [];

        $hasilOpsi = [];

        foreach ($opsiInput as $i => $o) {
            $lama = $o['gambar_lama'] ?? null;

            if (
                !$lama ||
                !in_array($lama, $gambarOpsiDiizinkan, true)
            ) {
                $lama = null;
            }

            $file = $o['gambar'] ?? null;
            $teks = $o['teks'] ?? '';

            if (blank($teks) && !$file && !$lama) {
                throw ValidationException::withMessages([
                    'opsi' =>
                        'Setiap opsi harus memiliki teks atau gambar.',
                ]);
            }

            $hasilOpsi[] = [
                'kode' => $tipe === 'benar_salah'
                    ? (string) ($i + 1)
                    : chr(65 + $i),

                'teks' => $teks,

                'is_benar' => $tipe === 'benar_salah'
                    ? (($o['bs'] ?? '0') === '1')
                    : in_array($i, $benar, true),

                '_file' => $file,
                '_lama' => $lama,
            ];
        }

        $data = [
            'mapel_id' => $v['mapel_id'],
            'tipe' => $tipe,
            'stimulus' => $v['stimulus'] ?? null,
            'pertanyaan' => $v['pertanyaan'],
            'poin' => Soal::POIN[$tipe],

            '_file' => $v['gambar'] ?? null,
            '_lama' => $gambarSoalLama,
        ];

        return [$data, $hasilOpsi];
    }

    private function terkunci(Soal $soal): bool
    {
        return DB::table('ujian_soal')
            ->join('sesi_ujian', 'sesi_ujian.ujian_id', '=', 'ujian_soal.ujian_id')
            ->where('ujian_soal.soal_id', $soal->id)
            ->exists();
    }
}