@php
    // Warna tombol. Tulis class secara utuh (jangan disambung).
    $warnaKembali = 'bg-gray-800 hover:bg-gray-700';

    // DIUBAH: batas dikunci di kode (UjianSiswaController::BATAS_PELANGGARAN), bukan dari kolom database
    $batas = \App\Http\Controllers\Siswa\UjianSiswaController::BATAS_PELANGGARAN;

    $jumlahDihitung = $log->where('dihitung', true)->count();
    $adaBatas = $batas > 0;
    $melewatiBatas = $adaBatas && $jumlahDihitung >= $batas;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Log Pelanggaran</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $sesi->user->name }}</h3>
                        <p class="text-sm text-gray-500">
                            NIS {{ $sesi->user->username }} &middot; {{ $ujian->judul }}
                        </p>
                    </div>
                    <span class="px-2 py-0.5 text-sm rounded-full bg-blue-100 text-blue-700">
                        Kelas {{ $sesi->user->kelas?->nama ?? '-' }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-center">
                    <div class="p-3 rounded {{ $melewatiBatas || $jumlahDihitung > 0 ? 'bg-red-50' : 'bg-gray-50' }}">
                        <div class="text-xl font-bold {{ $jumlahDihitung > 0 ? 'text-red-700' : '' }}">
                            {{-- DIUBAH: memakai $batas, bukan $ujian->batas_pelanggaran --}}
                            {{ $jumlahDihitung }}@if ($adaBatas) / {{ $batas }}@endif
                        </div>
                        <div class="text-xs text-gray-500">Dihitung</div>
                    </div>
                    <div class="p-3 rounded bg-gray-50">
                        <div class="text-xl font-bold">{{ $log->count() }}</div>
                        <div class="text-xs text-gray-500">Total catatan</div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b text-gray-600">
                            <th class="py-2">Waktu</th>
                            <th class="py-2">Jenis</th>
                            <th class="py-2">Keterangan</th>
                            <th class="py-2">Dihitung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($log as $l)
                            <tr class="border-b">
                                <td class="py-3 whitespace-nowrap">{{ $l->created_at->format('H:i:s') }}</td>
                                <td class="py-3 font-medium text-gray-900">{{ $label[$l->jenis] ?? $l->jenis }}</td>
                                <td class="py-3">{{ $l->keterangan ?? '-' }}</td>
                                <td class="py-3 whitespace-nowrap">
                                    @if ($l->dihitung)
                                        <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-semibold">Ya</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-600">Hanya dicatat</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-gray-500">Tidak ada catatan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <a href="{{ route('pengawas.pantau.index', $ujian) }}"
               class="inline-block px-4 py-2 {{ $warnaKembali }} text-white rounded-md text-sm">
                &larr; Kembali ke pemantauan
            </a>
        </div>
    </div>
</x-app-layout>