@php
    $pilihSoal = array_map('intval', old('soal_ids', $ujian->exists ? $ujian->soal->pluck('id')->all() : []));
    $pilihKelas = array_map('intval', old('kelas_ids', $ujian->exists ? $ujian->kelas->pluck('id')->all() : []));

    // Jadwal sesi: dari isian sebelumnya (jika validasi gagal), dari database, atau satu sesi kosong
    $sesiAwal = old('sesi');
    if ($sesiAwal === null) {
        $sesiAwal = $ujian->exists
            ? $ujian->jadwalEfektif()->map(fn ($j) => [
                'mulai_at' => $j->mulai_at->format('Y-m-d\TH:i'),
                'selesai_at' => $j->selesai_at->format('Y-m-d\TH:i'),
            ])->values()->all()
            : [];
    }
    if (empty($sesiAwal)) {
        $sesiAwal = [['mulai_at' => '', 'selesai_at' => '']];
    }
    $sesiAwal = collect($sesiAwal)->values()->map(fn ($s, $i) => [
        'k' => $i + 1,
        'mulai_at' => $s['mulai_at'] ?? '',
        'selesai_at' => $s['selesai_at'] ?? '',
    ])->all();

    // Pesan error per kolom sesi, mis. "sesi.0.selesai_at"
    $galatSesi = collect($errors->getMessages())
        ->filter(fn ($v, $k) => str_starts_with($k, 'sesi'))
        ->map(fn ($v) => $v[0])
        ->all();

    $init = [
        'mapelId' => (string) old('mapel_id', $ujian->mapel_id),
        'pilih' => $pilihSoal,
        'sesi' => $sesiAwal,
        'galat' => $galatSesi,
        'semua' => $daftarSoal->map(fn ($s) => [
            'id' => $s->id,
            'mapel_id' => (string) $s->mapel_id,
            'label' => \Illuminate\Support\Str::limit($s->pertanyaan, 90),
            'tipe' => $tipeLabel[$s->tipe],
            'poin' => $s->poin,
        ])->values()->all(),
    ];

    // Pesan error untuk kelompok field berbentuk array
    $errorKelas = array_merge(
        $errors->get('kelas_ids'),
        collect($errors->get('kelas_ids.*'))->flatten()->all()
    );
    $errorSoal = array_merge(
        $errors->get('soal_ids'),
        collect($errors->get('soal_ids.*'))->flatten()->all()
    );
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $ujian->exists ? 'Edit Ujian' : 'Buat Ujian' }}
        </h2>
    </x-slot>

    <script>
        function ujianForm(init) {
            return {
                mapelId: init.mapelId,
                pilih: init.pilih,
                semua: init.semua,

                // Jadwal sesi
                sesi: init.sesi,
                galat: init.galat,
                kunciBerikut: init.sesi.length + 1,

                tambahSesi() {
                    if (this.sesi.length >= 10) return;
                    this.sesi.push({ k: this.kunciBerikut++, mulai_at: '', selesai_at: '' });
                },
                hapusSesi(i) {
                    if (this.sesi.length <= 1) return;
                    this.sesi.splice(i, 1);
                    this.galat = {};
                },

                get tampil() { return this.semua.filter(s => s.mapel_id === this.mapelId); },
                get terpilih() { return this.tampil.filter(s => this.pilih.includes(s.id)); },
                get jumlah() { return this.terpilih.length; },
                get total() { return this.terpilih.reduce((a, s) => a + s.poin, 0); },

                toggle(id) {
                    this.pilih = this.pilih.includes(id)
                        ? this.pilih.filter(x => x !== id)
                        : [...this.pilih, id];
                },
                gantiMapel() {
                    const ids = this.tampil.map(s => s.id);
                    this.pilih = this.pilih.filter(id => ids.includes(id));
                },
                pilihSemua() { this.pilih = this.tampil.map(s => s.id); },
                kosongkan() { this.pilih = []; },
            };
        }
    </script>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Pesan error validasi --}}
            @if ($errors->any())
                <div role="alert" class="p-3 rounded bg-red-100 text-red-800 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $aksi }}" x-data="ujianForm(@js($init))" class="space-y-6">
                @csrf
                @if ($ujian->exists)
                    @method('PATCH')
                @endif

                {{-- ===================== Kartu 1: informasi ujian ===================== --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                    <h3 class="font-semibold text-gray-800">Informasi ujian</h3>

                    <div>
                        <x-input-label for="judul" value="Judul ujian" />
                        <x-text-input id="judul" name="judul" type="text" class="block mt-1 w-full"
                            :value="old('judul', $ujian->judul)" placeholder="Contoh: Penilaian Tengah Semester" required autofocus />
                        <x-input-error :messages="$errors->get('judul')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="mapel_id" value="Mata pelajaran" />
                            <select id="mapel_id" name="mapel_id" required x-model="mapelId"
                                    @change="$nextTick(() => gantiMapel())"
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- pilih mapel --</option>
                                @foreach ($mapel as $m)
                                    <option value="{{ $m->id }}">{{ $m->nama }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('mapel_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="durasi_menit" value="Durasi (menit)" />
                            <x-text-input id="durasi_menit" name="durasi_menit" type="number" min="5" max="300"
                                class="block mt-1 w-full" :value="old('durasi_menit', $ujian->durasi_menit)" required />
                            <x-input-error :messages="$errors->get('durasi_menit')" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- ===================== Kartu 2: jadwal sesi dan pengaturan ===================== --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                    <h3 class="font-semibold text-gray-800">Jadwal dan pengaturan</h3>

                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="font-medium text-sm text-gray-700">Jadwal sesi</span>
                            <button type="button" @click="tambahSesi()" x-show="sesi.length < 10"
                                    class="px-3 py-1.5 rounded bg-blue-600 text-white text-xs hover:bg-blue-700">
                                + Tambah sesi
                            </button>
                        </div>

                        <p class="mt-2 text-sm text-gray-600">
                            Siswa yang belum mengerjakan di sesi sebelumnya otomatis bisa masuk di sesi berikutnya.
                            Siswa hanya bisa memulai ujian di dalam rentang sesi yang sedang berjalan.
                            Lama pengerjaan tetap sesuai durasi, dan rentang tiap sesi minimal sepanjang durasi.
                        </p>

                        <div class="mt-3 space-y-3">
                            <template x-for="(s, i) in sesi" :key="s.k">
                                <div class="rounded-md border border-gray-200 p-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-semibold text-gray-800" x-text="'Sesi ' + (i + 1)"></span>
                                        <button type="button" @click="hapusSesi(i)" x-show="sesi.length > 1"
                                                class="text-xs text-red-600 hover:text-red-700">
                                            Hapus sesi
                                        </button>
                                    </div>

                                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block font-medium text-sm text-gray-700">Boleh mulai sejak</label>
                                            <input type="datetime-local" required x-model="s.mulai_at"
                                                   :name="'sesi[' + i + '][mulai_at]'"
                                                   class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                            <p class="mt-2 text-sm text-red-600"
                                               x-show="galat['sesi.' + i + '.mulai_at']"
                                               x-text="galat['sesi.' + i + '.mulai_at']"></p>
                                        </div>

                                        <div>
                                            <label class="block font-medium text-sm text-gray-700">Batas akhir mulai</label>
                                            <input type="datetime-local" required x-model="s.selesai_at"
                                                   :name="'sesi[' + i + '][selesai_at]'"
                                                   class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                            <p class="mt-2 text-sm text-red-600"
                                               x-show="galat['sesi.' + i + '.selesai_at']"
                                               x-text="galat['sesi.' + i + '.selesai_at']"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <x-input-error :messages="$errors->get('sesi')" class="mt-2" />
                    </div>

                    <label class="inline-flex items-center">
                        <input type="hidden" name="acak_soal" value="0">
                        <input type="checkbox" name="acak_soal" value="1" class="rounded border-gray-300"
                            @checked(old('acak_soal', $ujian->acak_soal))>
                        <span class="ml-2 text-sm text-gray-700">Acak urutan soal untuk setiap siswa</span>
                    </label>

                    <div>
                        <x-input-label for="batas_pelanggaran" value="Batas pelanggaran (0 = tidak dibatasi)" />
                        <x-text-input id="batas_pelanggaran" name="batas_pelanggaran" type="number" min="0" max="20"
                            class="block mt-1 w-40" :value="old('batas_pelanggaran', $ujian->batas_pelanggaran ?? 1)" required />
                        <x-input-error :messages="$errors->get('batas_pelanggaran')" class="mt-2" />

                        <p class="mt-2 text-sm text-gray-600">
                            Jika siswa meninggalkan halaman atau keluar layar penuh sebanyak ini, ujian diakhiri
                            dan jawaban dikirim otomatis. Dengan nilai 1, satu pelanggaran langsung mengakhiri ujian.
                            Nilai 0 berarti ujian tidak pernah diakhiri karena pelanggaran (hanya dicatat).
                        </p>

                        <div class="mt-3 p-3 rounded bg-yellow-50 border border-yellow-200 text-sm text-yellow-800">
                            Perhatian: pada nilai 1, kejadian tidak sengaja seperti notifikasi atau telepon masuk
                            juga langsung mengakhiri ujian siswa.
                        </div>
                    </div>
                </div>

                {{-- ===================== Kartu 3: kelas peserta ===================== --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800">Kelas peserta</h3>

                    <div class="mt-4 flex flex-wrap gap-3">
                        @forelse ($kelas as $k)
                            <label class="inline-flex items-center px-3 py-2 rounded border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="kelas_ids[]" value="{{ $k->id }}"
                                       class="rounded border-gray-300"
                                       @checked(in_array($k->id, $pilihKelas, true))>
                                <span class="ml-2 text-sm text-gray-700">{{ $k->nama }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-600">Belum ada kelas. Minta Admin menambahkannya.</p>
                        @endforelse
                    </div>

                    <x-input-error :messages="$errorKelas" class="mt-2" />
                </div>

                {{-- ===================== Kartu 4: pilih soal ===================== --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-semibold text-gray-800">Pilih soal dari bank soal</h3>

                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 whitespace-nowrap">
                                Terpilih: <strong x-text="jumlah"></strong> soal
                            </span>
                            <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 whitespace-nowrap">
                                Total: <strong x-text="total"></strong> poin
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded text-sm text-gray-700" x-show="! mapelId">
                        Pilih mata pelajaran terlebih dahulu.
                    </div>

                    <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded text-sm text-gray-700"
                         x-show="mapelId && tampil.length === 0">
                        Belum ada soal Anda pada mapel ini di bank soal.
                    </div>

                    <div class="mt-4 flex gap-2" x-show="tampil.length > 0">
                        <button type="button" @click="pilihSemua()"
                                class="px-4 py-2 rounded bg-blue-600 text-white text-xs hover:bg-blue-700">
                            Pilih semua
                        </button>
                        <button type="button" @click="kosongkan()"
                                class="px-4 py-2 rounded bg-gray-600 text-white text-xs hover:bg-gray-700">
                            Kosongkan
                        </button>
                    </div>

                    <div class="mt-3 max-h-96 overflow-y-auto border border-gray-200 rounded-md divide-y" x-show="tampil.length > 0">
                        <template x-for="s in tampil" :key="s.id">
                            <label class="flex items-start gap-3 p-3 cursor-pointer"
                                   :class="pilih.includes(s.id) ? 'bg-green-50' : 'hover:bg-gray-50'">
                                <input type="checkbox" name="soal_ids[]" :value="s.id"
                                       :checked="pilih.includes(s.id)" @change="toggle(s.id)"
                                       class="mt-1 rounded border-gray-300">
                                <span class="flex-1 text-sm text-gray-700">
                                    <span x-text="s.label"></span>
                                    <span class="ml-2 px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 whitespace-nowrap" x-text="s.tipe"></span>
                                    <span class="ml-1 px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 whitespace-nowrap" x-text="s.poin + ' poin'"></span>
                                </span>
                            </label>
                        </template>
                    </div>

                    <x-input-error :messages="$errorSoal" class="mt-2" />
                </div>

                {{-- ===================== Aksi ===================== --}}
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ route('guru.ujian.index') }}"
                       class="inline-block text-gray-100 rounded-sm bg-violet-700 p-2 py-3">
                        &larr; Kembali ke daftar ujian
                    </a>

                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                        {{ $ujian->exists ? 'Simpan perubahan' : 'Simpan ujian' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Notifikasi SweetAlert --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (! window.Swal) return;

            @if (session('status'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: @js(session('status')),
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Ujian belum tersimpan',
                    text: @js($errors->first()),
                    confirmButtonColor: '#2563eb',
                });
            @endif
        });
    </script>
</x-app-layout>