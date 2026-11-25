@php
    // Warna tombol aksi. Tulis class secara utuh (jangan disambung).
    $warnaAksi = [
        'absensi' => 'bg-purple-600 hover:bg-purple-700',
        'pantau'  => 'bg-green-600 hover:bg-green-700',
        'token'   => 'bg-blue-600 hover:bg-blue-700',
    ];

    // Warna badge fase ujian (kunci huruf kecil, sesuai strtolower($u->fase))
    $warnaFase = [
        'belum dimulai'   => 'bg-blue-100 text-blue-700',
        'menunggu jadwal' => 'bg-blue-100 text-blue-700',
        'berlangsung'     => 'bg-green-100 text-green-700',
        'selesai'         => 'bg-gray-200 text-gray-700',
        'waktu habis'     => 'bg-orange-100 text-orange-700',
        'ditutup'         => 'bg-gray-200 text-gray-700',
    ];

    // Tombol versi tabel (desktop) dan versi kartu (HP)
    $tombol   = 'inline-block px-3 py-1.5 text-white rounded-md text-xs';
    $tombolHp = 'flex items-center justify-center px-3 py-2.5 text-white rounded-md text-sm text-center';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Ujian</h2>
    </x-slot>

    {{-- Pembungkus: tidak boleh melebihi lebar layar, teks panjang dibungkus --}}
    <div class="py-4 sm:py-12 overflow-x-hidden">
        <div class="w-full max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 space-y-3 sm:space-y-6 break-words">

            {{-- Sapaan --}}
            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <p class="text-gray-800">
                    Halo, <strong>{{ auth()->user()->name }}</strong>
                    <span class="inline-block ml-1 sm:ml-2 px-2 py-0.5 text-xs sm:text-sm rounded-full bg-blue-100 text-blue-700">
                        {{ auth()->user()->role }}
                    </span>
                </p>
            </div>

            {{-- ===== TAMPILAN HP: kartu per ujian ===== --}}
            <div class="md:hidden space-y-3">
                @forelse ($ujian as $u)
                    @php
                        $warnaBadge = $warnaFase[strtolower((string) $u->fase)] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <div class="bg-white shadow-sm rounded-lg p-4 space-y-3 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $u->judul }}</h3>
                                <p class="text-sm text-gray-600">{{ $u->mapel->nama }}</p>
                            </div>
                            <span class="shrink-0 inline-block px-2 py-0.5 text-xs rounded-full {{ $warnaBadge }}">
                                {{ $u->fase }}
                            </span>
                        </div>

                        <dl class="text-sm space-y-2">
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 shrink-0">Jadwal</dt>
                                <dd class="text-right text-gray-800 min-w-0">
                                    {{ $u->mulai_at->format('d M Y H:i') }}<br>
                                    <span class="text-gray-500">s/d {{ $u->selesai_at->format('d M Y H:i') }}</span>
                                </dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 shrink-0">Token dipakai</dt>
                                <dd class="text-gray-800">{{ $u->token_dipakai_count }} / {{ $u->token_count }}</dd>
                            </div>
                        </dl>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <a href="{{ route('absensi.index', $u) }}"
                               class="{{ $tombolHp }} {{ $warnaAksi['absensi'] }} col-span-2">
                                Absensi &amp; Berita Acara
                            </a>
                            @if ($u->status === 'aktif')
                                <a href="{{ route('pengawas.pantau.index', $u) }}"
                                   class="{{ $tombolHp }} {{ $warnaAksi['pantau'] }}">
                                    Pantau
                                </a>
                                <a href="{{ route('token.index', $u) }}"
                                   class="{{ $tombolHp }} {{ $warnaAksi['token'] }}">
                                    Token
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white shadow-sm rounded-lg p-4 text-sm text-gray-500">
                        Belum ada ujian.
                    </div>
                @endforelse
            </div>

            {{-- ===== TAMPILAN TABLET/DESKTOP: tabel (bisa digeser ke samping di dalam kartu) ===== --}}
            <div class="hidden md:block bg-white shadow-sm rounded-lg p-6 overflow-x-auto max-w-full">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b text-gray-600">
                            <th class="py-2 pr-3">Judul</th>
                            <th class="py-2 pr-3">Mapel</th>
                            <th class="py-2 pr-3">Jadwal</th>
                            <th class="py-2 pr-3">Status</th>
                            <th class="py-2 pr-3">Token dipakai</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ujian as $u)
                            @php
                                $warnaBadge = $warnaFase[strtolower((string) $u->fase)] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <tr class="border-b align-top">
                                <td class="py-3 pr-3 font-medium text-gray-900 break-words">{{ $u->judul }}</td>
                                <td class="py-3 pr-3">{{ $u->mapel->nama }}</td>
                                <td class="py-3 pr-3 whitespace-nowrap">
                                    {{ $u->mulai_at->format('d M Y H:i') }}<br>
                                    <span class="text-gray-500">s/d {{ $u->selesai_at->format('d M Y H:i') }}</span>
                                </td>
                                <td class="py-3 pr-3">
                                    <span class="px-2 py-0.5 rounded-full whitespace-nowrap {{ $warnaBadge }}">{{ $u->fase }}</span>
                                </td>
                                <td class="py-3 pr-3 whitespace-nowrap">{{ $u->token_dipakai_count }} / {{ $u->token_count }}</td>
                                <td class="py-3 text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('absensi.index', $u) }}"
                                           class="{{ $tombol }} {{ $warnaAksi['absensi'] }}">
                                            Absensi &amp; Berita Acara
                                        </a>
                                        @if ($u->status === 'aktif')
                                            <a href="{{ route('pengawas.pantau.index', $u) }}"
                                               class="{{ $tombol }} {{ $warnaAksi['pantau'] }}">
                                                Pantau
                                            </a>
                                            <a href="{{ route('token.index', $u) }}"
                                               class="{{ $tombol }} {{ $warnaAksi['token'] }}">
                                                Token
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-gray-500">Belum ada ujian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Tombol kembali --}}
            <a href="{{ route('pengawas.dashboard') }}"
               class="flex sm:inline-flex items-center justify-center px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-white rounded-md text-sm">
                &larr; Kembali ke dashboard
            </a>
        </div>
    </div>
</x-app-layout>