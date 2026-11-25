<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Import Siswa dari CSV</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6 print:hidden">
                <ol class="list-decimal list-inside text-gray-700 space-y-1 text-sm">
                    <li>Unduh template, lalu buka dengan Excel.</li>
                    <li>Isi kolom <strong>nis</strong>, <strong>nama</strong>, <strong>kelas</strong>. Kolom <strong>password</strong> boleh kosong (dibuat acak 6 angka).</li>
                    <li>Simpan lewat <strong>Save As</strong> dengan tipe <strong>CSV</strong> (sebaiknya "CSV UTF-8").</li>
                    <li>Upload file di bawah ini. Sebaiknya maksimal sekitar 300 siswa per file.</li>
                </ol>

                <a href="{{ route('admin.import.siswa.template') }}"
                   class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                    &darr; Unduh template CSV
                </a>

                {{-- BARU: konfirmasi lewat SweetAlert --}}
                <form method="POST" action="{{ route('admin.import.siswa.store') }}"
                      enctype="multipart/form-data" class="mt-4 flex flex-wrap gap-3 items-center"
                      data-judul="Import siswa?"
                      data-konfirmasi="Akun siswa akan dibuat dari isi file CSV ini."
                      data-ikon="question"
                      data-tombol="Ya, import">
                    @csrf
                    <input type="file" name="file" accept=".csv,.txt" required class="text-sm">
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-green-700">
                        Import
                    </button>
                </form>
            </div>

            @if (session('hasil'))
                @php($hasil = session('hasil'))

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center">
                        <h3 class="font-semibold text-gray-800">
                            Berhasil: {{ count($hasil['dibuat']) }} &middot; Dilewati: {{ count($hasil['dilewati']) }}
                        </h3>
                        @if (count($hasil['dibuat']))
                            <button type="button" id="tombol-cetak"
                                    class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 print:hidden">
                                Cetak daftar
                            </button>
                        @endif
                    </div>

                    @if (count($hasil['dibuat']))
                        <p class="mt-3 text-sm text-yellow-800 bg-yellow-100 rounded p-2 print:hidden">
                            Password acak hanya ditampilkan sekarang. Cetak atau catat sebelum meninggalkan halaman ini.
                        </p>
                        <table class="w-full text-left text-sm mt-4">
                            <thead>
                                <tr class="border-b bg-gray-100 text-gray-700">
                                    <th class="py-2 px-3">NIS</th>
                                    <th class="py-2 px-3">Nama</th>
                                    <th class="py-2 px-3">Kelas</th>
                                    <th class="py-2 px-3">Password</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($hasil['dibuat'] as $b)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-1 px-3">{{ $b['nis'] }}</td>
                                        <td class="py-1 px-3">{{ $b['nama'] }}</td>
                                        <td class="py-1 px-3">{{ $b['kelas'] }}</td>
                                        <td class="py-1 px-3 font-mono">{{ $b['password'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                @if (count($hasil['dilewati']))
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 print:hidden">
                        <h3 class="font-semibold text-red-700">Baris yang dilewati</h3>
                        <table class="w-full text-left text-sm mt-3">
                            <thead>
                                <tr class="border-b bg-red-50 text-red-800">
                                    <th class="py-2 px-3">Baris</th>
                                    <th class="py-2 px-3">NIS</th>
                                    <th class="py-2 px-3">Alasan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($hasil['dilewati'] as $l)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-1 px-3">{{ $l['baris'] }}</td>
                                        <td class="py-1 px-3">{{ $l['nis'] }}</td>
                                        <td class="py-1 px-3">{{ $l['alasan'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif

            <a href="{{ route('admin.users.index') }}" class="inline-block text-gray-100 rounded-sm bg-violet-700 p-2 py-3 print:hidden">&larr; Kembali ke Kelola Akun</a>
        </div>
    </div>

    {{-- BARU: notifikasi SweetAlert --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Import gagal',
                    text: @js($errors->first()),
                    confirmButtonColor: '#2563eb',
                });
            @endif

            @if (session('hasil'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: {{ count(session('hasil')['dibuat']) > 0 ? "'success'" : "'warning'" }},
                    title: 'Berhasil {{ count(session('hasil')['dibuat']) }}, dilewati {{ count(session('hasil')['dilewati']) }}',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                });
            @endif

            const cetak = document.getElementById('tombol-cetak');
            if (cetak) {
                cetak.addEventListener('click', () => {
                    Swal.fire({
                        title: 'Cetak daftar akun?',
                        text: 'Password acak hanya tampil sekali. Pastikan daftar dicetak atau dicatat.',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, cetak',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#6b7280',
                        reverseButtons: true,
                    }).then((h) => { if (h.isConfirmed) window.print(); });
                });
            }
        });
    </script>
</x-app-layout>