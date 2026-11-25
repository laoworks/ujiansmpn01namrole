<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Import Soal dari Excel
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Pesan sukses --}}
            @if (session('success'))
                <div role="alert" class="p-3 rounded bg-green-100 text-green-800 text-sm flex flex-wrap items-center justify-between gap-2">
                    <span>{{ session('success') }}</span>
                    <a href="{{ route('guru.soal.index') }}" class="underline font-semibold">Lihat bank soal</a>
                </div>
            @endif

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

            {{-- Error dari isi file --}}
            @if (session('import_errors'))
                <div role="alert" class="p-3 rounded bg-red-100 text-red-800 text-sm">
                    <p class="font-semibold mb-1">Import dibatalkan, tidak ada soal yang tersimpan. Perbaiki hal berikut lalu unggah ulang:</p>
                    <ul class="list-disc list-inside">
                        @foreach (session('import_errors') as $err)
                            <li>
                                @if (is_array($err))
                                    {{ implode(' - ', array_map(fn ($v) => is_scalar($v) ? $v : json_encode($v), $err)) }}
                                @else
                                    {{ $err }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ===================== Kartu 1: langkah dan template ===================== --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h3 class="font-semibold text-gray-800">Cara import</h3>
                        <p class="mt-1 text-sm text-gray-600">Ikuti empat langkah berikut.</p>
                    </div>

                    <a href="{{ route('guru.soal.import.template', 'xlsx') }}"
                       class="inline-block px-4 py-2 rounded bg-gray-600 text-white text-sm hover:bg-gray-700">
                        &darr; Unduh template Excel
                    </a>
                </div>

                <ol class="mt-4 space-y-3 text-sm text-gray-700">
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold">1</span>
                        <span>Unduh template. Sheet <strong>Contoh</strong> berisi contoh tiap tipe soal dan sheet <strong>Petunjuk</strong> berisi penjelasan kolom.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold">2</span>
                        <span>Isi soal di sheet <strong>Soal</strong> mulai baris 2. Satu baris adalah satu soal. Jangan mengubah nama kolom di baris 1.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold">3</span>
                        <span>Simpan file dalam format <strong>.xlsx</strong>.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold">4</span>
                        <span>Pilih mata pelajaran, lalu unggah file di kartu berikutnya. Maksimal 500 soal per file dan ukuran file maksimal 5 MB.</span>
                    </li>
                </ol>

                <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded text-sm text-gray-700">
                    Jika satu baris saja salah, <strong>seluruh file ditolak</strong> dan tidak ada soal yang tersimpan.
                    Gambar tidak bisa diimpor lewat file ini. Tambahkan lewat tombol <strong>Edit</strong> setelah soal masuk.
                </div>
            </div>

            {{-- ===================== Kartu 2: unggah file ===================== --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800">Unggah file</h3>

                <form id="form-import" method="POST" action="{{ route('guru.soal.import.store') }}"
                      enctype="multipart/form-data"
                      class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    @csrf

                    <div>
                        <x-input-label for="mapel_id" value="Mata pelajaran" />
                        <select id="mapel_id" name="mapel_id" required
                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">-- pilih mapel --</option>
                            @foreach ($mapel as $m)
                                <option value="{{ $m->id }}" @selected((string) old('mapel_id') === (string) $m->id)>{{ $m->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('mapel_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="file" value="File Excel (.xlsx)" />
                        <input id="file" type="file" name="file" accept=".xlsx" required class="block mt-1 text-sm">
                        <x-input-error :messages="$errors->get('file')" class="mt-2" />
                    </div>

                    <div>
                        <button id="tombol-import" type="submit"
                                class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm hover:bg-indigo-700 disabled:opacity-60">
                            Import soal
                        </button>
                    </div>
                </form>
            </div>

            {{-- ===================== Kartu 3: format kolom ===================== --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800">Format kolom</h3>
                <p class="mt-1 text-sm text-gray-600">Nama kolom di baris 1 harus sama persis dengan tabel berikut.</p>

                <div class="overflow-x-auto mt-4">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b bg-gray-100 text-gray-700">
                                <th class="py-2 px-3">Kolom</th>
                                <th class="py-2 px-3">Wajib?</th>
                                <th class="py-2 px-3">Isi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr class="border-b hover:bg-gray-50 align-top">
                                <td class="py-2 px-3 font-mono">tipe</td>
                                <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 whitespace-nowrap">Ya</span></td>
                                <td class="py-2 px-3">
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 whitespace-nowrap"><code>pg</code> pilihan ganda</span>
                                        <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 whitespace-nowrap"><code>pg_kompleks</code> pilihan ganda kompleks</span>
                                        <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 whitespace-nowrap"><code>benar_salah</code> benar/salah</span>
                                    </div>
                                </td>
                            </tr>

                            <tr class="border-b hover:bg-gray-50 align-top">
                                <td class="py-2 px-3 font-mono">stimulus</td>
                                <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 whitespace-nowrap">Tidak</span></td>
                                <td class="py-2 px-3">Bacaan atau teks pendukung soal. Boleh dikosongkan.</td>
                            </tr>

                            <tr class="border-b hover:bg-gray-50 align-top">
                                <td class="py-2 px-3 font-mono">pertanyaan</td>
                                <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 whitespace-nowrap">Ya</span></td>
                                <td class="py-2 px-3">Teks pertanyaan.</td>
                            </tr>

                            <tr class="border-b hover:bg-gray-50 align-top">
                                <td class="py-2 px-3 font-mono">poin</td>
                                <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 whitespace-nowrap">Tidak</span></td>
                                <td class="py-2 px-3">
                                    Angka 1 sampai 255. Disarankan:
                                    <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 whitespace-nowrap"><code>pg</code> 1 poin</span>
                                    <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 whitespace-nowrap"><code>pg_kompleks</code> 2 poin</span>
                                    <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 whitespace-nowrap"><code>benar_salah</code> 3 poin</span>
                                    <br>Jika kosong, dipakai nilai bawaan sistem.
                                </td>
                            </tr>

                            <tr class="border-b hover:bg-gray-50 align-top">
                                <td class="py-2 px-3 font-mono whitespace-nowrap">opsi_a sampai opsi_e</td>
                                <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 whitespace-nowrap">Ya</span></td>
                                <td class="py-2 px-3">
                                    <code>pg</code> dan <code>pg_kompleks</code>: pilihan jawaban, minimal 2, isi berurutan mulai dari <code>opsi_a</code>.<br>
                                    <code>benar_salah</code>: setiap kolom berisi <strong>satu pernyataan</strong>, minimal 1.
                                </td>
                            </tr>

                            <tr class="hover:bg-gray-50 align-top">
                                <td class="py-2 px-3 font-mono">kunci</td>
                                <td class="py-2 px-3"><span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 whitespace-nowrap">Ya</span></td>
                                <td class="py-2 px-3">
                                    <code>pg</code>: satu huruf, contoh <code>B</code>.<br>
                                    <code>pg_kompleks</code>: dua huruf atau lebih, contoh <code>A,C</code>.<br>
                                    <code>benar_salah</code>: <code>B</code> (benar) atau <code>S</code> (salah) untuk tiap pernyataan sesuai urutan, contoh <code>B,S,B</code>.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Kembali --}}
            <a href="{{ route('guru.soal.index') }}"
               class="inline-block text-gray-100 rounded-sm bg-violet-700 p-2 py-3">
                &larr; Kembali ke bank soal
            </a>
        </div>
    </div>

    <script>
        // Cegah klik ganda saat file sedang diproses
        document.getElementById('form-import').addEventListener('submit', function () {
            const tombol = document.getElementById('tombol-import');
            tombol.disabled = true;
            tombol.textContent = 'Memproses...';
        });
    </script>
</x-app-layout>