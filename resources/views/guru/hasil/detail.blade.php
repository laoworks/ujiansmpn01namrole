<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Jawaban Siswa</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
            @endif

            {{-- Kartu identitas siswa + nilai --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap gap-3 items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $sesi->user->name }}</h3>
                        <p class="text-sm text-gray-500">
                            NIS {{ $sesi->user->username }} &middot; Kelas {{ $sesi->user->kelas?->nama ?? '-' }}
                            &middot; {{ $ujian->judul }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-gray-900">
                            {{ $sesi->nilai !== null ? number_format($sesi->nilai, 2) : '-' }}
                        </div>
                        <div class="text-sm text-gray-500">Skor {{ $sesi->skor ?? '-' }} / {{ $sesi->skor_maks ?? '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Catatan pelanggaran --}}
            @if ($log->isNotEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                    <h4 class="font-semibold text-gray-800 mb-3">
                        Catatan pelanggaran ({{ $log->where('dihitung', true)->count() }} dihitung
                        @if ($ujian->batas_pelanggaran > 0) dari batas {{ $ujian->batas_pelanggaran }} @endif)
                    </h4>
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b bg-gray-100 text-gray-700">
                                <th class="py-2 px-3">Waktu</th>
                                <th class="py-2 px-3">Jenis</th>
                                <th class="py-2 px-3">Keterangan</th>
                                <th class="py-2 px-3">Dihitung</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($log as $l)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-3 whitespace-nowrap">{{ $l->created_at->format('H:i:s') }}</td>
                                    <td class="py-2 px-3">{{ $labelLog[$l->jenis] ?? $l->jenis }}</td>
                                    <td class="py-2 px-3">{{ $l->keterangan ?? '-' }}</td>
                                    <td class="py-2 px-3">
                                        @if ($l->dihitung)
                                            <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700">Ya</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 whitespace-nowrap">Hanya dicatat</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Kartu per soal --}}
            @foreach ($baris as $b)
                @php
                    $s = $b['soal'];
                    $garis = ! $b['dijawab'] ? 'border-gray-300' : ($b['benar'] ? 'border-green-500' : 'border-red-500');
                @endphp

                <div class="bg-white shadow-sm sm:rounded-lg p-6 border-l-4 {{ $garis }}">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold">No. {{ $loop->iteration }}</span>
                            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">{{ $tipeLabel[$s->tipe] }}</span>

                            @if (! $b['dijawab'])
                                <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 whitespace-nowrap">Tidak dijawab</span>
                            @elseif ($b['benar'])
                                <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700">Benar</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700">Salah</span>
                            @endif

                            @if ($b['ragu'])
                                <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 whitespace-nowrap">Ditandai ragu-ragu</span>
                            @endif
                        </div>
                        <span class="font-semibold whitespace-nowrap">{{ $b['poin'] }} / {{ $s->poin }} poin</span>
                    </div>

                    @if ($s->stimulus)
                        <div class="mt-3 p-3 bg-gray-50 rounded text-gray-700 text-sm whitespace-pre-line">{{ $s->stimulus }}</div>
                    @endif

                    @if ($s->gambar_url)
                        <img src="{{ $s->gambar_url }}" alt="Gambar soal" class="mt-3 max-h-60 rounded border">
                    @endif

                    <p class="mt-3 font-medium text-gray-900 whitespace-pre-line">{{ $s->pertanyaan }}</p>

                    <dl class="mt-3 text-sm space-y-1">
                        <div>
                            <dt class="inline text-gray-500">Jawaban siswa:</dt>
                            <dd class="inline font-semibold {{ ! $b['dijawab'] ? 'text-gray-500' : ($b['benar'] ? 'text-green-700' : 'text-red-700') }}">
                                {{ $b['jawaban'] }}
                            </dd>
                        </div>
                        <div>
                            <dt class="inline text-gray-500">Kunci:</dt>
                            <dd class="inline font-semibold text-green-700">{{ $b['kunci'] }}</dd>
                        </div>
                    </dl>
                </div>
            @endforeach

            <a href="{{ route('guru.hasil.index', $ujian) }}" class="inline-block text-gray-100 rounded-sm bg-violet-700 p-2 py-3">&larr; Kembali ke hasil ujian</a>
        </div>
    </div>
</x-app-layout>