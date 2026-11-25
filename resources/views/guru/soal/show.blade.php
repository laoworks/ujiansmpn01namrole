<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Soal
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Pesan sukses --}}
            @if (session('status'))
                <div role="alert" class="p-3 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Pesan error validasi --}}
            @if ($errors->any())
                <div role="alert" class="p-3 rounded bg-red-100 text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Kartu detail soal --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                {{-- Badge info --}}
                <div class="flex flex-wrap gap-2 text-sm">
                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 whitespace-nowrap">
                        {{ $soal->mapel->nama }}
                    </span>

                    <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 whitespace-nowrap">
                        {{ $tipeLabel[$soal->tipe] ?? $soal->tipe }}
                    </span>

                    <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 whitespace-nowrap">
                        {{ $soal->poin }} poin
                    </span>
                </div>

                {{-- Stimulus --}}
                @if ($soal->stimulus)
                    <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded text-gray-700 whitespace-pre-line">{{ $soal->stimulus }}</div>
                @endif

                {{-- Gambar soal --}}
                @if ($soal->gambar_url)
                    <img
                        src="{{ $soal->gambar_url }}"
                        alt="Gambar soal"
                        class="mt-4 max-h-80 rounded border"
                    >
                @endif

                {{-- Pertanyaan --}}
                <p class="mt-4 text-gray-900 font-medium whitespace-pre-line">{{ $soal->pertanyaan }}</p>

                {{-- Daftar opsi --}}
                <ul class="mt-4 space-y-2">
                    @foreach ($soal->opsi as $o)
                        <li class="flex items-start gap-3 p-2 rounded {{ $soal->tipe !== 'benar_salah' && $o->is_benar ? 'bg-green-50 border border-green-200' : 'border border-transparent' }}">
                            <span class="w-6 font-semibold text-gray-700">{{ $o->kode }}</span>

                            <div class="flex-1">
                                @if ($o->teks !== '')
                                    <span>{{ $o->teks }}</span>
                                @endif

                                @if ($o->gambar_url)
                                    <img
                                        src="{{ $o->gambar_url }}"
                                        alt="Gambar opsi {{ $o->kode }}"
                                        class="mt-1 max-h-40 rounded border"
                                    >
                                @endif
                            </div>

                            @if ($soal->tipe === 'benar_salah')
                                <span class="font-semibold {{ $o->is_benar ? 'text-green-700' : 'text-red-700' }}">
                                    {{ $o->is_benar ? 'Benar' : 'Salah' }}
                                </span>
                            @elseif ($o->is_benar)
                                <span class="text-green-700 font-semibold whitespace-nowrap">&#10003; Kunci</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Aksi --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a
                    href="{{ route('guru.soal.index') }}"
                    class="inline-block px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700"
                >
                    &larr; Kembali ke bank soal
                </a>

                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('guru.soal.edit', $soal) }}"
                        class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700"
                    >
                        Edit
                    </a>

                    <form
                        method="POST"
                        action="{{ route('guru.soal.destroy', $soal) }}"
                        class="inline"
                        onsubmit="return confirm('Hapus soal ini?')"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700"
                        >
                            Hapus
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>