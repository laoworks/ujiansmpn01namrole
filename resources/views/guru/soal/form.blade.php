@php
    $edit = $soal->exists;
    $opsiLama = $edit ? $soal->opsi->values() : collect();
    $tipeAwal = old('tipe', $soal->tipe ?: 'pg');
    $urlGambar = fn ($p) => $p ? route('media.soal', basename($p)) : null;

    // Kunci pilihan ganda (indeks opsi, mulai dari 0)
    $kunciPg = old('benar_pg');
    if ($kunciPg === null && $soal->tipe === 'pg') {
        $idx = $opsiLama->search(fn ($o) => (bool) $o->is_benar);
        $kunciPg = $idx === false ? null : $idx;
    }

    // Kunci pilihan ganda kompleks (dua indeks)
    $kunciKompleks = old('benar_kompleks');
    if ($kunciKompleks === null) {
        $kunciKompleks = $soal->tipe === 'pg_kompleks'
            ? $opsiLama->keys()->filter(fn ($i) => (bool) $opsiLama[$i]->is_benar)->values()->all()
            : [];
    }
    $kunciKompleks = array_map('intval', (array) $kunciKompleks);

    // Jumlah pernyataan awal untuk benar/salah (2 sampai 6)
    $jumlahOpsiAwal = is_array(old('opsi')) ? count(old('opsi')) : $opsiLama->count();
    $jumlahBs = $tipeAwal === 'benar_salah' ? min(6, max(2, $jumlahOpsiAwal)) : 3;

    $gambarSoalLama = old('gambar_lama', $soal->gambar);

    // Pesan error untuk bagian opsi
    $errorOpsi = array_merge(
        $errors->get('opsi'),
        collect($errors->get('opsi.*'))->flatten()->all(),
        $errors->get('benar_pg'),
        $errors->get('benar_kompleks'),
    );
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $edit ? 'Edit Soal' : 'Tambah Soal' }}
        </h2>
    </x-slot>

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

            <form
                id="form-soal"
                method="POST"
                action="{{ $aksi }}"
                enctype="multipart/form-data"
                data-jumlah-bs="{{ $jumlahBs }}"
                class="space-y-6"
            >
                @csrf
                @if ($edit)
                    @method('PUT')
                @endif

                {{-- ===================== Kartu 1: informasi soal ===================== --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="mapel_id" value="Mata pelajaran" />
                            <select id="mapel_id" name="mapel_id" required
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- pilih mapel --</option>
                                @foreach ($mapel as $m)
                                    <option value="{{ $m->id }}"
                                        @selected((string) old('mapel_id', $soal->mapel_id) === (string) $m->id)>
                                        {{ $m->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('mapel_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tipe" value="Tipe soal" />
                            <select id="tipe" name="tipe" required
                                    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach ($tipeLabel as $val => $label)
                                    <option value="{{ $val }}" @selected($tipeAwal === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-sm text-gray-600">
                                Poin otomatis:
                                <span id="badge-poin"
                                      class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 whitespace-nowrap"></span>
                            </p>
                            <x-input-error :messages="$errors->get('tipe')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Stimulus --}}
                    <div>
                        <x-input-label for="stimulus" value="Stimulus / bacaan" />
                        <textarea id="stimulus" name="stimulus" rows="3"
                                  class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('stimulus', $soal->stimulus) }}</textarea>
                        <p class="mt-1 text-sm text-gray-600">Wajib untuk soal benar/salah, opsional untuk tipe lain.</p>
                        <x-input-error :messages="$errors->get('stimulus')" class="mt-2" />
                    </div>

                    {{-- Gambar soal --}}
                    <div>
                        <x-input-label for="gambar" value="Gambar soal" />

                        @if ($gambarSoalLama)
                            <div class="mt-2">
                                <img src="{{ $urlGambar($gambarSoalLama) }}" alt="Gambar soal saat ini"
                                     class="max-h-40 rounded border">
                                <label class="inline-flex items-center gap-2 mt-2 text-sm text-gray-700">
                                    <input type="checkbox" data-hapus class="rounded border-gray-300">
                                    Hapus gambar ini
                                </label>
                                <input type="hidden" name="gambar_lama" value="{{ $gambarSoalLama }}" data-lama>
                            </div>
                        @endif

                        <input id="gambar" type="file" name="gambar"
                               accept="image/png,image/jpeg,image/webp" class="block mt-1 text-sm">
                        <p class="mt-1 text-sm text-gray-600">
                            Opsional. Format JPG, PNG, atau WEBP, maksimal 1 MB.
                            @if ($gambarSoalLama) Mengunggah gambar baru akan menggantikan gambar saat ini. @endif
                        </p>
                        <x-input-error :messages="$errors->get('gambar')" class="mt-2" />
                    </div>

                    {{-- Pertanyaan --}}
                    <div>
                        <x-input-label for="pertanyaan" value="Pertanyaan" />
                        <textarea id="pertanyaan" name="pertanyaan" rows="3" required
                                  class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
                        <x-input-error :messages="$errors->get('pertanyaan')" class="mt-2" />
                    </div>
                </div>

                {{-- ===================== Kartu 2: opsi jawaban ===================== --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800">Opsi jawaban</h3>
                    <p class="mt-1 text-sm text-gray-600" data-hint="pg">Isi 4 opsi, lalu pilih satu kunci jawaban.</p>
                    <p class="mt-1 text-sm text-gray-600" data-hint="pg_kompleks">Isi 4 opsi, lalu centang tepat dua jawaban yang benar.</p>
                    <p class="mt-1 text-sm text-gray-600" data-hint="benar_salah">Isi 2 sampai 6 pernyataan, lalu tentukan Benar atau Salah untuk tiap pernyataan.</p>

                    <ul class="mt-4 space-y-2">
                        @for ($i = 0; $i < 6; $i++)
                            @php
                                $o = $opsiLama[$i] ?? null;
                                $teks = old("opsi.$i.teks", $o?->teks ?? '');
                                $lama = old("opsi.$i.gambar_lama", $o?->gambar);
                                $bs = old(
                                    "opsi.$i.bs",
                                    ($soal->tipe === 'benar_salah' && $o) ? ($o->is_benar ? '1' : '0') : null
                                );
                            @endphp

                            <li data-row class="flex items-start gap-3 p-3 rounded border border-gray-200">
                                <span data-label class="w-6 pt-2 font-semibold text-gray-700"></span>

                                <div class="flex-1 space-y-2">
                                    <x-text-input type="text" name="opsi[{{ $i }}][teks]" :value="$teks"
                                                  maxlength="1000" class="block w-full text-sm" />

                                    @if ($lama)
                                        <div>
                                            <img src="{{ $urlGambar($lama) }}" alt="Gambar opsi saat ini"
                                                 class="max-h-24 rounded border">
                                            <label class="inline-flex items-center gap-2 mt-1 text-sm text-gray-700">
                                                <input type="checkbox" data-hapus class="rounded border-gray-300">
                                                Hapus gambar ini
                                            </label>
                                            <input type="hidden" name="opsi[{{ $i }}][gambar_lama]"
                                                   value="{{ $lama }}" data-lama>
                                        </div>
                                    @endif

                                    <input type="file" name="opsi[{{ $i }}][gambar]"
                                           accept="image/png,image/jpeg,image/webp" class="block text-sm">
                                </div>

                                {{-- Kunci / benar-salah: di sebelah kanan opsi --}}
                                <div class="shrink-0 pt-2">
                                    {{-- Kunci: pilihan ganda --}}
                                    <div data-sec="pg">
                                        <label class="inline-flex items-center gap-2 text-sm text-green-700 font-semibold whitespace-nowrap">
                                            <input type="radio" name="benar_pg" value="{{ $i }}" required
                                                   @checked($kunciPg !== null && (string) $kunciPg === (string) $i)>
                                            &#10003; Kunci
                                        </label>
                                    </div>

                                    {{-- Kunci: pilihan ganda kompleks --}}
                                    <div data-sec="pg_kompleks">
                                        <label class="inline-flex items-center gap-2 text-sm text-green-700 font-semibold whitespace-nowrap">
                                            <input type="checkbox" name="benar_kompleks[]" value="{{ $i }}"
                                                   class="rounded border-gray-300"
                                                   @checked(in_array($i, $kunciKompleks, true))>
                                            &#10003; Kunci
                                        </label>
                                    </div>

                                    {{-- Kunci: benar/salah --}}
                                    <div data-sec="benar_salah" class="flex flex-col items-start gap-1 text-sm font-semibold">
                                        <label class="inline-flex items-center gap-2 text-green-700">
                                            <input type="radio" name="opsi[{{ $i }}][bs]" value="1" required
                                                   @checked((string) $bs === '1')>
                                            Benar
                                        </label>
                                        <label class="inline-flex items-center gap-2 text-red-700">
                                            <input type="radio" name="opsi[{{ $i }}][bs]" value="0" required
                                                   @checked((string) $bs === '0')>
                                            Salah
                                        </label>
                                    </div>
                                </div>
                            </li>
                        @endfor
                    </ul>

                    <x-input-error :messages="$errorOpsi" class="mt-2" />

                    {{-- Tambah / kurangi pernyataan (hanya benar/salah) --}}
                    <div id="kontrol-bs" class="mt-4 flex gap-2">
                        <button type="button" id="tambah-pernyataan"
                                class="px-4 py-2 rounded bg-green-600 text-white text-xs hover:bg-green-700">
                            + Tambah pernyataan
                        </button>
                        <button type="button" id="kurangi-pernyataan"
                                class="px-4 py-2 rounded bg-red-600 text-white text-xs hover:bg-red-700">
                            &minus; Kurangi pernyataan
                        </button>
                    </div>
                </div>

                {{-- ===================== Aksi ===================== --}}
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ route('guru.soal.index') }}"
                       class="inline-block text-gray-100 rounded-sm bg-violet-700 p-2 py-3">
                        &larr; Kembali ke bank soal
                    </a>

                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                        {{ $edit ? 'Simpan perubahan' : 'Simpan soal' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const POIN = @js(\App\Models\Soal::POIN);

            const form = document.getElementById('form-soal');
            const tipe = document.getElementById('tipe');
            const stimulus = document.getElementById('stimulus');
            const badgePoin = document.getElementById('badge-poin');
            const rows = Array.from(form.querySelectorAll('[data-row]'));
            const kontrolBs = document.getElementById('kontrol-bs');
            let jumlahBs = parseInt(form.dataset.jumlahBs, 10) || 3;

            // Sorot opsi yang menjadi kunci (hijau), seperti di halaman detail soal
            function sorot(mode) {
                rows.forEach(function (row) {
                    let kunci = false;
                    if (mode === 'pg') {
                        const r = row.querySelector('[data-sec="pg"] input');
                        kunci = !!(r && r.checked && !r.disabled);
                    } else if (mode === 'pg_kompleks') {
                        const c = row.querySelector('[data-sec="pg_kompleks"] input');
                        kunci = !!(c && c.checked && !c.disabled);
                    }
                    row.classList.toggle('bg-green-50', kunci);
                    row.classList.toggle('border-green-200', kunci);
                    row.classList.toggle('border-gray-200', !kunci);
                });
            }

            function render() {
                const mode = tipe.value;
                const bs = mode === 'benar_salah';
                const n = bs ? jumlahBs : 4;

                stimulus.required = bs;
                kontrolBs.classList.toggle('hidden', !bs);
                badgePoin.textContent = (POIN[mode] ?? '-') + ' poin';

                document.querySelectorAll('[data-hint]').forEach(function (el) {
                    el.classList.toggle('hidden', el.dataset.hint !== mode);
                });

                rows.forEach(function (row, i) {
                    const aktif = i < n;
                    row.classList.toggle('hidden', !aktif);
                    row.querySelector('[data-label]').textContent =
                        bs ? String(i + 1) : String.fromCharCode(65 + i);

                    row.querySelectorAll('[data-sec]').forEach(function (sec) {
                        sec.classList.toggle('hidden', sec.dataset.sec !== mode);
                    });

                    // Input pada baris tidak aktif atau bagian yang tidak sesuai tipe tidak dikirim
                    row.querySelectorAll('input').forEach(function (el) {
                        const sec = el.closest('[data-sec]');
                        let mati = !aktif;
                        if (sec) mati = mati || sec.dataset.sec !== mode;
                        if (el.hasAttribute('data-lama')) {
                            const cb = el.parentElement.querySelector('[data-hapus]');
                            mati = mati || (cb && cb.checked);
                        }
                        el.disabled = mati;
                    });
                });

                // Gambar soal: jika "hapus gambar" dicentang, gambar lama tidak dikirim
                const lamaSoal = form.querySelector('input[name="gambar_lama"]');
                if (lamaSoal) {
                    const cb = lamaSoal.parentElement.querySelector('[data-hapus]');
                    lamaSoal.disabled = !!(cb && cb.checked);
                }

                sorot(mode);
            }

            tipe.addEventListener('change', render);

            document.getElementById('tambah-pernyataan').addEventListener('click', function () {
                if (jumlahBs < 6) { jumlahBs++; render(); }
            });
            document.getElementById('kurangi-pernyataan').addEventListener('click', function () {
                if (jumlahBs > 2) { jumlahBs--; render(); }
            });

            form.addEventListener('change', function (e) {
                if (e.target.matches('[data-hapus]')) render();

                // Pilihan ganda kompleks: maksimal dua jawaban benar
                if (e.target.name === 'benar_kompleks[]' && e.target.checked) {
                    const dipilih = form.querySelectorAll('input[name="benar_kompleks[]"]:checked:not(:disabled)');
                    if (dipilih.length > 2) e.target.checked = false;
                }

                if (e.target.name === 'benar_pg' || e.target.name === 'benar_kompleks[]') {
                    sorot(tipe.value);
                }
            });

            render();
        })();
    </script>
</x-app-layout>