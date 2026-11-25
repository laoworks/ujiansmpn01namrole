<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelola {{ $judul }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route($rute.'.store') }}" class="flex gap-3">
                    @csrf
                    <x-text-input name="nama" type="text" class="flex-1"
                        placeholder="Nama {{ strtolower($judul) }} baru" :value="old('nama')" required />
                    <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-green-700">
                        Tambah
                    </button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b bg-gray-100 text-gray-700">
                            <th class="py-2 px-3">Nama</th>
                            @if ($tampilSiswa)
                                <th class="py-2 px-3">Jumlah siswa</th>
                            @endif
                            <th class="py-2 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar as $item)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3">{{ $item->nama }}</td>
                                @if ($tampilSiswa)
                                    <td class="py-2 px-3">{{ $item->siswa_count }}</td>
                                @endif
                                <td class="py-2 px-3 text-right whitespace-nowrap">
                                    <a href="{{ route($rute.'.edit', $item) }}"
                                       class="inline-block px-4 py-2 rounded bg-blue-600 text-white text-xs hover:bg-blue-700 mr-1">
                                        Edit
                                    </a>
                                    {{-- BARU: konfirmasi lewat SweetAlert --}}
                                    <form method="POST" action="{{ route($rute.'.destroy', $item) }}"
                                          class="inline"
                                          data-judul="Hapus {{ strtolower($judul) }}?"
                                          data-konfirmasi="{{ $item->nama }} akan dihapus dan tidak bisa dikembalikan."
                                          data-tombol="Ya, hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-4 py-2 rounded bg-red-600 text-white text-xs hover:bg-red-700">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $tampilSiswa ? 3 : 2 }}" class="py-4 px-3 text-gray-500">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="inline-block text-gray-100 rounded-sm bg-violet-700 p-2 py-3">&larr; Kembali ke dashboard</a>
        </div>
    </div>

    {{-- BARU: notifikasi SweetAlert --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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
                    title: 'Tidak berhasil',
                    text: @js($errors->first()),
                    confirmButtonColor: '#2563eb',
                });
            @endif
        });
    </script>
</x-app-layout>