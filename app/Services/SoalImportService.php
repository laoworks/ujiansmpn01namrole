<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

/**
 * Import soal dari template Excel (.xlsx) atau Word (.docx) ke tabel `soal` dan `opsi_jawaban`.
 * Semua-atau-tidak-sama-sekali: jika ada satu soal yang salah, tidak ada yang disimpan.
 */
class SoalImportService
{
    private const MAKS_SOAL = 500;
    private const HURUF     = ['A', 'B', 'C', 'D', 'E'];

    /** @return array{berhasil:int, errors:string[]} */
    public function import(UploadedFile $file, int $mapelId, int $guruId): array
    {
        $ext = strtolower($file->getClientOriginalExtension());

        try {
            $items = $ext === 'docx'
                ? (new DocxSoalReader())->baca($file->getRealPath())
                : $this->bacaExcel($file->getRealPath());
        } catch (RuntimeException $e) {
            return $this->gagal([$e->getMessage()]);
        } catch (\Throwable $e) {
            return $this->gagal(['File tidak dapat dibaca. Pastikan memakai template yang disediakan.']);
        }

        if (count($items) > self::MAKS_SOAL) {
            return $this->gagal(['Maksimal ' . self::MAKS_SOAL . ' soal per file.']);
        }

        // Validasi semua soal
        $daftar = [];
        $errors = [];

        foreach ($items as $item) {
            $data = $item['data'];
            $get  = fn (string $kolom) => trim((string) ($data[$kolom] ?? ''));

            // Penanda tipe tidak dikenali sudah punya pesan sendiri, tidak perlu divalidasi lagi
            if ($get('tipe') === '' && ! empty($item['pesan'])) {
                [$soal, $opsi, $pesan] = [null, null, []];
            } else {
                [$soal, $opsi, $pesan] = $this->parseSoal($get);
            }

            $pesan = array_merge($item['pesan'] ?? [], $pesan);

            if ($pesan) {
                foreach ($pesan as $p) {
                    $errors[] = "{$item['label']}: {$p}";
                }
                continue;
            }

            $daftar[] = [$soal, $opsi, $item['gambar'] ?? null];
        }

        if ($errors) {
            return $this->gagal($errors);
        }

        if (! $daftar) {
            return $this->gagal(['Tidak ada soal yang ditemukan.']);
        }

        // Simpan
        $fileTersimpan = [];

        try {
            DB::transaction(function () use ($daftar, $mapelId, $guruId, &$fileTersimpan) {
                $now = now();

                foreach ($daftar as [$soal, $opsi, $gambar]) {
                    $soalId = DB::table('soal')->insertGetId([
                        'mapel_id'   => $mapelId,
                        'guru_id'    => $guruId,
                        'tipe'       => $soal['tipe'],
                        'stimulus'   => $soal['stimulus'],
                        'gambar'     => $this->simpanGambar($gambar['soal'] ?? null, $mapelId, $fileTersimpan),
                        'pertanyaan' => $soal['pertanyaan'],
                        'poin'       => $soal['poin'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('opsi_jawaban')->insert(array_map(fn ($o) => [
                        'soal_id'    => $soalId,
                        'kode'       => $o['kode'],
                        'teks'       => $o['teks'],
                        'gambar'     => $this->simpanGambar($gambar['opsi'][$o['huruf']] ?? null, $mapelId, $fileTersimpan),
                        'is_benar'   => $o['is_benar'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], $opsi));
                }
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($fileTersimpan); // bersihkan gambar yang sempat tersimpan
            report($e);

            return $this->gagal(['Terjadi kesalahan saat menyimpan soal. Tidak ada soal yang tersimpan.']);
        }

        return ['berhasil' => count($daftar), 'errors' => []];
    }

    /**
     * Baca sheet "Soal" (atau sheet pertama) dari file Excel.
     * @return array<int, array{label:string, data:array, gambar:null, pesan:string[]}>
     */
    private function bacaExcel(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getSheetByName('Soal') ?? $spreadsheet->getSheet(0);
        $rows        = $sheet->toArray(null, true, true, false); // $rows[0] = baris 1 Excel

        if (count($rows) < 2) {
            throw new RuntimeException('File kosong. Isi soal mulai baris 2 pada sheet "Soal".');
        }

        $peta   = $this->petaKolom($rows[0]);
        $kurang = array_diff(['tipe', 'pertanyaan', 'kunci', 'opsi_a', 'opsi_b'], array_keys($peta));
        if ($kurang) {
            throw new RuntimeException('Kolom tidak ditemukan: ' . implode(', ', $kurang) . '. Gunakan template terbaru.');
        }

        $items = [];
        foreach ($rows as $i => $row) {
            if ($i === 0 || $this->barisKosong($row)) {
                continue;
            }

            $data = [];
            foreach ($peta as $nama => $idx) {
                $data[$nama] = trim((string) ($row[$idx] ?? ''));
            }

            $items[] = ['label' => 'Baris ' . ($i + 1), 'data' => $data, 'gambar' => null, 'pesan' => []];
        }

        if (! $items) {
            throw new RuntimeException('Tidak ada soal yang ditemukan di sheet "Soal".');
        }

        return $items;
    }

    /** @return array{0:?array,1:?array,2:string[]} [soal, opsi, pesan error] */
    private function parseSoal(callable $get): array
    {
        $pesan = [];

        $tipe = self::normalisasiTipe($get('tipe'));
        if (! $tipe) {
            return [null, null, ['tipe harus pg, pg_kompleks, atau benar_salah.']];
        }

        $pertanyaan = $get('pertanyaan');
        if ($pertanyaan === '') {
            $pesan[] = 'pertanyaan wajib diisi.';
        }

        // poin
        $poinStr = $get('poin');
        if ($poinStr === '') {
            $poin = $tipe === 'pg' ? 1 : 2;
        } elseif (ctype_digit($poinStr) && (int) $poinStr >= 1 && (int) $poinStr <= 255) {
            $poin = (int) $poinStr;
        } else {
            $poin    = 1;
            $pesan[] = 'poin harus angka bulat 1-255.';
        }

        // opsi (harus terisi berurutan dari opsi_a)
        $teksOpsi = [];
        foreach (self::HURUF as $huruf) {
            $teks = $get('opsi_' . strtolower($huruf));
            if ($teks !== '') {
                $teksOpsi[$huruf] = $teks;
            }
        }

        if (array_keys($teksOpsi) !== array_slice(self::HURUF, 0, count($teksOpsi))) {
            $pesan[] = 'opsi harus diisi berurutan mulai dari A tanpa ada yang terlewat di tengah.';
        }

        $minimal = $tipe === 'benar_salah' ? 1 : 2;
        if (count($teksOpsi) < $minimal) {
            $pesan[] = $tipe === 'benar_salah'
                ? 'isi minimal 1 pernyataan.'
                : 'isi minimal 2 pilihan jawaban (A dan B).';
        }

        // kunci
        $token = preg_split('/[\s,;]+/', strtoupper($get('kunci')), -1, PREG_SPLIT_NO_EMPTY);
        if (! $token) {
            $pesan[] = 'kunci wajib diisi.';
        }

        if ($pesan) {
            return [null, null, $pesan];
        }

        $opsi = [];

        if ($tipe === 'benar_salah') {
            if (count($token) !== count($teksOpsi)) {
                return [null, null, ['jumlah kunci (' . count($token) . ') harus sama dengan jumlah pernyataan (' . count($teksOpsi) . ').']];
            }

            $n = 0;
            foreach ($teksOpsi as $huruf => $teks) {
                $t = $token[$n];
                if (! in_array($t, ['B', 'S', 'BENAR', 'SALAH'], true)) {
                    return [null, null, ['kunci ke-' . ($n + 1) . " (\"{$t}\") harus B (Benar) atau S (Salah)."]];
                }
                $n++;
                $opsi[] = ['kode' => "P{$n}", 'huruf' => $huruf, 'teks' => $teks, 'is_benar' => in_array($t, ['B', 'BENAR'], true)];
            }
        } else {
            $kunci = array_values(array_unique($token));

            foreach ($kunci as $k) {
                if (! isset($teksOpsi[$k])) {
                    return [null, null, ["kunci \"{$k}\" tidak punya opsi yang terisi."]];
                }
            }

            if ($tipe === 'pg' && count($kunci) !== 1) {
                return [null, null, ['soal pg harus punya tepat 1 kunci (contoh: B).']];
            }

            if ($tipe === 'pg_kompleks' && count($kunci) < 2) {
                return [null, null, ['soal pg_kompleks harus punya minimal 2 kunci (contoh: A,C).']];
            }

            foreach ($teksOpsi as $huruf => $teks) {
                $opsi[] = ['kode' => $huruf, 'huruf' => $huruf, 'teks' => $teks, 'is_benar' => in_array($huruf, $kunci, true)];
            }
        }

        return [
            ['tipe' => $tipe, 'stimulus' => $get('stimulus') ?: null, 'pertanyaan' => $pertanyaan, 'poin' => $poin],
            $opsi,
            [],
        ];
    }

    public static function normalisasiTipe(string $tipe): ?string
    {
        $t = strtolower(preg_replace('/[\s\/\-]+/', '_', trim($tipe)));

        return match ($t) {
            'pg', 'pilihan_ganda'                   => 'pg',
            'pg_kompleks', 'pilihan_ganda_kompleks' => 'pg_kompleks',
            'benar_salah', 'bs'                     => 'benar_salah',
            default                                 => null,
        };
    }

    /** @param ?array{ext:string,data:string} $gambar */
    private function simpanGambar(?array $gambar, int $mapelId, array &$fileTersimpan): ?string
    {
        if (! $gambar) {
            return null;
        }

        $path = "soal/{$mapelId}/" . Str::uuid() . '.' . $gambar['ext'];
        Storage::disk('public')->put($path, $gambar['data']);
        $fileTersimpan[] = $path;

        return $path;
    }

    /** nama kolom (huruf kecil, spasi jadi _) => indeks kolom */
    private function petaKolom(array $header): array
    {
        $peta = [];
        foreach ($header as $idx => $nama) {
            $key = strtolower(preg_replace('/\s+/', '_', trim((string) $nama)));
            if ($key !== '') {
                $peta[$key] = $idx;
            }
        }

        return $peta;
    }

    private function barisKosong(array $row): bool
    {
        foreach ($row as $v) {
            if (trim((string) $v) !== '') {
                return false;
            }
        }

        return true;
    }

    private function gagal(array $errors): array
    {
        return ['berhasil' => 0, 'errors' => $errors];
    }
}