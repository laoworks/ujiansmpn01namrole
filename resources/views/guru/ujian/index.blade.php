<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Ujian
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Ringkasan dan tombol buat ujian --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap gap-3 items-center justify-between">
                    <p class="text-sm text-gray-600">
                        Total <span class="font-semibold text-gray-800">{{ $ujian->total() }}</span> ujian
                    </p>

                    <a
                        href="{{ route('guru.ujian.create') }}"
                        class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700"
                    >
                        + Buat Ujian
                    </a>
                </div>
            </div>

            {{-- Tabel ujian --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-gray-100 text-gray-700">
                            <th class="py-2 px-3">Judul</th>
                            <th class="py-2 px-3">Mapel</th>
                            <th class="py-2 px-3">Jadwal</th>
                            <th class="py-2 px-3">Durasi</th>
                            <th class="py-2 px-3">Soal</th>
                            <th class="py-2 px-3">Kelas</th>
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($ujian as $u)
                            @php
                                [$warna, $titik] = match ($u->fase) {
                                    'Draft' => ['bg-gray-100 text-gray-700', 'bg-gray-400'],
                                    'Berlangsung' => ['bg-green-100 text-green-700', 'bg-green-500'],
                                    'Menunggu jadwal' => ['bg-blue-100 text-blue-700', 'bg-blue-500'],
                                    default => ['bg-orange-100 text-orange-700', 'bg-orange-500'],
                                };
                            @endphp

                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3 font-semibold text-gray-800">
                                    {{ $u->judul }}
                                </td>

                                <td class="py-2 px-3">
                                    {{ $u->mapel->nama }}
                                </td>

                                <td class="py-2 px-3 whitespace-nowrap">
                                    {{ $u->mulai_at->format('d M Y H:i') }}<br>
                                    <span class="text-xs text-gray-500">
                                        s/d {{ $u->selesai_at->format('d M Y H:i') }}
                                    </span>
                                </td>

                                <td class="py-2 px-3 whitespace-nowrap">
                                    {{ $u->durasi_menit }} menit
                                </td>

                                <td class="py-2 px-3">
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 whitespace-nowrap">
                                        {{ $u->soal_count }}
                                    </span>
                                </td>

                                <td class="py-2 px-3">
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 whitespace-nowrap">
                                        {{ $u->kelas_count }}
                                    </span>
                                </td>

                                <td class="py-2 px-3">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full whitespace-nowrap {{ $warna }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $titik }}"></span>
                                        {{ $u->fase }}
                                    </span>
                                </td>

                                <td class="py-2 px-3 text-right whitespace-nowrap">
                                    <a
                                        href="{{ route('guru.ujian.show', $u) }}"
                                        class="inline-block px-4 py-2 rounded bg-gray-600 text-white text-xs hover:bg-gray-700 mr-1"
                                    >
                                        Lihat
                                    </a>

                                    @if ($u->status === 'draft')
                                        <a
                                            href="{{ route('guru.ujian.edit', $u) }}"
                                            class="inline-block px-4 py-2 rounded bg-blue-600 text-white text-xs hover:bg-blue-700 mr-1"
                                        >
                                            Edit
                                        </a>

                                        {{-- BARU: konfirmasi lewat SweetAlert --}}
                                        <form
                                            method="POST"
                                            action="{{ route('guru.ujian.destroy', $u) }}"
                                            class="inline"
                                            data-judul="Hapus ujian?"
                                            data-konfirmasi="Ujian &quot;{{ $u->judul }}&quot; akan dihapus dan tidak bisa dikembalikan."
                                            data-tombol="Ya, hapus"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="px-4 py-2 rounded bg-red-600 text-white text-xs hover:bg-red-700"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 px-3 text-center">
                                    <p class="text-gray-500">Belum ada ujian.</p>

                                    <a
                                        href="{{ route('guru.ujian.create') }}"
                                        class="inline-block mt-3 text-sm text-blue-600 hover:text-blue-700"
                                    >
                                        Buat ujian pertama &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                @if ($ujian->hasPages())
                    <div class="mt-4">
                        {{ $ujian->links() }}
                    </div>
                @endif
            </div>

            {{-- Kembali ke dashboard --}}
            <a
                href="{{ route('guru.dashboard') }}"
                class="inline-block px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700"
            >
                &larr; Kembali ke dashboard
            </a>

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