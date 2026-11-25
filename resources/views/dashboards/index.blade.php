@php
    // Warna per tombol, berdasarkan label. Tulis class secara utuh (jangan disambung).
    $warnaLabel = [
        'Kelola Akun'           => 'bg-blue-600 hover:bg-blue-700',
        'Import Siswa'          => 'bg-green-600 hover:bg-green-700',
        'Kelola Kelas'          => 'bg-orange-500 hover:bg-orange-600',
        'Kelola Mata Pelajaran' => 'bg-purple-600 hover:bg-purple-700',
    ];

    // Cadangan untuk label lain (menu guru, pengawas, siswa), dipakai bergiliran.
    $warnaCadangan = [
        'bg-teal-600 hover:bg-teal-700',
        'bg-indigo-600 hover:bg-indigo-700',
        'bg-rose-600 hover:bg-rose-700',
        'bg-cyan-600 hover:bg-cyan-700',
        'bg-amber-500 hover:bg-amber-600',
        'bg-gray-800 hover:bg-gray-700',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $judul }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <p class="text-gray-800">
                    Halo, <strong>{{ auth()->user()->name }}</strong>

                    <span class="ml-2 px-2 py-0.5 text-sm rounded-full bg-blue-100 text-blue-700">
                        {{ auth()->user()->role }}
                    </span>
                </p>

                <p class="mt-4 text-gray-600">
                    Fitur yang akan dibangun di tahap berikutnya:
                </p>

                <ul class="mt-2 list-disc list-inside text-gray-600">
                    @foreach ($menu as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>

                @if (!empty($links))
                    <div class="mt-6 flex flex-wrap gap-3">
                        @foreach ($links as $label => $url)
                            @php
                                $warna = $warnaLabel[$label]
                                    ?? $warnaCadangan[$loop->index % count($warnaCadangan)];
                            @endphp
                            <a
                                href="{{ $url }}"
                                class="px-4 py-2 {{ $warna }} text-white rounded-md text-sm"
                            >
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>