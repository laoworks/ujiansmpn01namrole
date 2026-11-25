<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Ujian Selesai
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12 overflow-x-hidden">
        <div class="w-full max-w-xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-5 sm:p-8 text-center break-words">

                <div class="text-5xl text-green-600">&#10003;</div>

                <h3 class="mt-3 text-lg font-semibold text-gray-900">
                    Jawaban Anda telah dikirim
                </h3>

                <p class="mt-1 text-gray-600">
                    {{ $ujian->judul }} &middot; {{ $ujian->mapel->nama }}
                </p>

                <p class="mt-4 text-sm text-gray-700">
                    Soal terjawab:
                    <strong>{{ $terjawab }}</strong>
                    dari
                    <strong>{{ $total }}</strong>
                </p>

                @if ($sesi->log()->where('jenis', 'keluar_aplikasi')->exists())
                    <p class="mt-3 text-sm text-red-600">
                        Ujian diakhiri otomatis karena Anda meninggalkan aplikasi ujian.
                    </p>
                @endif

                {{-- Tampilkan nilai jika diizinkan --}}
                @if ($tampilNilai && $sesi->nilai !== null)
                    <div class="mt-4 p-4 rounded bg-blue-50">
                        <div class="text-sm text-gray-600">Nilai Anda</div>
                        <div class="text-4xl font-bold text-blue-700">
                            {{ number_format($sesi->nilai, 2) }}
                        </div>
                        <div class="text-xs text-gray-500">
                            Skor {{ $sesi->skor }} dari {{ $sesi->skor_maks }}
                        </div>
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-500">
                        Nilai akan diumumkan oleh guru.
                    </p>
                @endif

                {{-- Tombol kembali: biru, selebar layar di HP --}}
                <a href="{{ route('siswa.dashboard') }}"
                   class="block sm:inline-block w-full sm:w-auto mt-6 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm text-center">
                    Kembali ke dashboard
                </a>

            </div>
        </div>
    </div>
</x-app-layout>