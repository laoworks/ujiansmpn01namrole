<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Bank Soal
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filter dan tombol tambah soal --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap gap-3 items-center justify-between">

                    <form method="GET" class="flex flex-wrap gap-2">
                        <select
                            name="mapel_id"
                            class="border-gray-300 rounded-md shadow-sm"
                        >
                            <option value="">Semua mapel</option>

                            @foreach ($mapel as $m)
                                <option
                                    value="{{ $m->id }}"
                                    @selected((string) request('mapel_id') === (string) $m->id)
                                >
                                    {{ $m->nama }}
                                </option>
                            @endforeach
                        </select>

                        <select
                            name="tipe"
                            class="border-gray-300 rounded-md shadow-sm"
                        >
                            <option value="">Semua tipe</option>

                            @foreach ($tipeLabel as $val => $label)
                                <option
                                    value="{{ $val }}"
                                    @selected(request('tipe') === $val)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <x-text-input
                            name="q"
                            type="text"
                            placeholder="Cari pertanyaan"
                            :value="request('q')"
                        />

                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-blue-700"
                        >
                            Cari
                        </button>
                    </form>

                    <div class="flex gap-2">
                        <a
                            href="{{ route('guru.soal.import') }}"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700"
                        >
                            Import dari Excel
                        </a>

                        <a
                            href="{{ route('guru.soal.create') }}"
                            class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700"
                        >
                            + Tambah Soal
                        </a>
                    </div>
                </div>
            </div>

            {{-- Tabel bank soal --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-gray-100 text-gray-700">
                            <th class="py-2 px-3">Pertanyaan</th>
                            <th class="py-2 px-3">Mapel</th>
                            <th class="py-2 px-3">Tipe</th>
                            <th class="py-2 px-3">Poin</th>
                            <th class="py-2 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($soal as $s)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3">
                                    {{ \Illuminate\Support\Str::limit($s->pertanyaan, 80) }}
                                </td>

                                <td class="py-2 px-3">
                                    {{ $s->mapel->nama }}
                                </td>

                                <td class="py-2 px-3">
                                    <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 whitespace-nowrap">
                                        {{ $tipeLabel[$s->tipe] ?? $s->tipe }}
                                    </span>
                                </td>

                                <td class="py-2 px-3">
                                    {{ $s->poin }}
                                </td>

                                <td class="py-2 px-3 text-right whitespace-nowrap">
                                    <a
                                        href="{{ route('guru.soal.show', $s) }}"
                                        class="inline-block px-4 py-2 rounded bg-gray-600 text-white text-xs hover:bg-gray-700 mr-1"
                                    >
                                        Lihat
                                    </a>

                                    <a
                                        href="{{ route('guru.soal.edit', $s) }}"
                                        class="inline-block px-4 py-2 rounded bg-blue-600 text-white text-xs hover:bg-blue-700 mr-1"
                                    >
                                        Edit
                                    </a>

                                    {{-- Konfirmasi lewat SweetAlert --}}
                                    <form
                                        method="POST"
                                        action="{{ route('guru.soal.destroy', $s) }}"
                                        class="inline"
                                        data-judul="Hapus soal?"
                                        data-konfirmasi="Soal &quot;{{ \Illuminate\Support\Str::limit(strip_tags($s->pertanyaan), 60) }}&quot; akan dihapus dan tidak bisa dikembalikan."
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
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 px-3 text-gray-500">
                                    Belum ada soal.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $soal->links() }}
                </div>
            </div>

            {{-- Kembali ke dashboard --}}
            <a
                href="{{ route('guru.dashboard') }}"
                class="inline-block text-gray-100 rounded-sm bg-violet-700 p-2 py-3"
            >
                &larr; Kembali ke dashboard
            </a>

        </div>
    </div>

    {{-- Notifikasi SweetAlert --}}
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