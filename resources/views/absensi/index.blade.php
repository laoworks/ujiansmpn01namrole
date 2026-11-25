@php
    $kembali = auth()->user()->hasRole('guru')
        ? route('guru.ujian.show', $ujian)
        : route('pengawas.ujian.index');

    /*
    |--------------------------------------------------------------------------
    | Gaya bersama (sama dengan halaman Hasil via WhatsApp).
    | Semua class ditulis utuh (jangan disambung) agar terbaca Tailwind.
    |--------------------------------------------------------------------------
    */

    // Kartu, judul, dan teks bantuan
    $kartu      = 'bg-white shadow-sm rounded-lg p-4 sm:p-6';
    $judulKartu = 'text-base font-semibold text-gray-900';
    $bantuan    = 'text-xs text-gray-500';

    // Badge
    $badge      = 'inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full';
    $badgeBiru  = $badge . ' bg-blue-100 text-blue-700';
    $badgeHijau = $badge . ' bg-green-100 text-green-700';
    $badgeAbu   = $badge . ' bg-gray-200 text-gray-600';

    // Badge fase ujian (disesuaikan dengan nilai $ujian->fase)
    $warnaFase = [
        'belum dimulai' => 'bg-blue-100 text-blue-700',
        'berlangsung'   => 'bg-green-100 text-green-700',
        'selesai'       => 'bg-gray-200 text-gray-700',
    ];
    $warnaFaseIni = $warnaFase[strtolower((string) $ujian->fase)] ?? 'bg-gray-100 text-gray-700';

    // Tombol: dasar bersama
    $fokus = 'transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-400';

    // Tombol kecil di tabel (desktop) dan tombol besar di kartu (HP)
    $tombol   = 'inline-flex items-center justify-center px-3 py-1 text-white rounded-md text-xs font-medium ' . $fokus;
    $tombolHp = 'flex items-center justify-center px-3 py-2.5 text-white rounded-md text-sm font-medium text-center ' . $fokus;

    // Warna tombol
    $warnaIsi     = 'bg-blue-600 hover:bg-blue-700';
    $warnaHadir   = 'bg-teal-600 hover:bg-teal-700';
    $warnaBa      = 'bg-purple-600 hover:bg-purple-700';
    $warnaKembali = 'bg-gray-800 hover:bg-gray-700';

    // Tombol kembali: sama dengan tombol standar halaman lain (px-4 py-2)
    $tombolKembali = 'flex sm:inline-flex items-center justify-center px-4 py-2 text-white rounded-md text-sm font-medium '
                   . $fokus . ' ' . $warnaKembali;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Absensi &amp; Berita Acara</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            {{-- Notifikasi sukses/error ditangani SweetAlert (lihat skrip di bawah) --}}

            {{-- Sapaan --}}
            <div class="{{ $kartu }}">
                <p class="text-gray-800 break-words">
                    Halo, <strong>{{ auth()->user()->name }}</strong>
                    <span class="{{ $badgeBiru }} ml-1 sm:ml-2">{{ auth()->user()->role }}</span>
                </p>
            </div>

            {{-- Info ujian --}}
            <div class="{{ $kartu }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="{{ $judulKartu }} sm:text-lg break-words">{{ $ujian->judul }}</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $ujian->mapel->nama }} &middot; {{ $ujian->mulai_at->format('d M Y H:i') }}
                        </p>
                    </div>
                    <span class="{{ $badge }} {{ $warnaFaseIni }} shrink-0">{{ $ujian->fase }}</span>
                </div>
                <p class="mt-3 text-sm text-gray-600">
                    Isi absensi dan berita acara untuk setiap kelas (ruang) sebelum ujian dimulai.
                    Catatan selama ujian bisa dilengkapi setelah ujian selesai.
                </p>
            </div>

            {{-- ===== TAMPILAN HP: kartu per kelas ===== --}}
            <div class="md:hidden space-y-3">
                @forelse ($baris as $b)
                    <div class="{{ $kartu }} space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <span class="{{ $badgeBiru }} text-sm">{{ $b['kelas']->nama }}</span>
                            <span class="text-sm text-gray-600">{{ $b['jumlah'] }} peserta</span>
                        </div>

                        <dl class="text-sm space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500">Absensi</dt>
                                <dd>
                                    @if ($b['dicek'] > 0)
                                        <span class="{{ $badgeHijau }}">{{ $b['hadir'] }} hadir</span>
                                    @else
                                        <span class="{{ $badgeAbu }}">Belum diisi</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500">Berita acara</dt>
                                <dd>
                                    @if ($b['ba'])
                                        <span class="{{ $badgeHijau }}">Tersimpan</span>
                                    @else
                                        <span class="{{ $badgeAbu }}">Belum diisi</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <a href="{{ route('absensi.form', [$ujian, $b['kelas']]) }}"
                               class="{{ $tombolHp }} {{ $warnaIsi }} col-span-2">Isi</a>
                            <a href="{{ route('absensi.cetak.hadir', [$ujian, $b['kelas']]) }}" target="_blank" rel="noopener"
                               class="{{ $tombolHp }} {{ $warnaHadir }} {{ $b['ba'] ? '' : 'col-span-2' }}">Daftar hadir</a>
                            @if ($b['ba'])
                                <a href="{{ route('absensi.cetak.ba', [$ujian, $b['kelas']]) }}" target="_blank" rel="noopener"
                                   class="{{ $tombolHp }} {{ $warnaBa }}">Berita acara</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="{{ $kartu }} text-center text-sm text-gray-500">
                        Ujian ini belum memiliki kelas peserta.
                    </div>
                @endforelse
            </div>

            {{-- ===== TAMPILAN TABLET/DESKTOP: tabel ===== --}}
            <div class="hidden md:block {{ $kartu }}">
                <h4 class="{{ $judulKartu }}">Daftar kelas</h4>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-600">
                                <th class="px-3 py-2">Kelas (ruang)</th>
                                <th class="px-3 py-2">Peserta</th>
                                <th class="px-3 py-2">Absensi</th>
                                <th class="px-3 py-2">Berita acara</th>
                                <th class="px-3 py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($baris as $b)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-3">
                                        <span class="{{ $badgeBiru }}">{{ $b['kelas']->nama }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-gray-700">{{ $b['jumlah'] }}</td>
                                    <td class="px-3 py-3">
                                        @if ($b['dicek'] > 0)
                                            <span class="{{ $badgeHijau }}">{{ $b['hadir'] }} hadir</span>
                                        @else
                                            <span class="{{ $badgeAbu }}">Belum diisi</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">
                                        @if ($b['ba'])
                                            <span class="{{ $badgeHijau }}">Tersimpan</span>
                                        @else
                                            <span class="{{ $badgeAbu }}">Belum diisi</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('absensi.form', [$ujian, $b['kelas']]) }}"
                                               class="{{ $tombol }} {{ $warnaIsi }}">Isi</a>
                                            <a href="{{ route('absensi.cetak.hadir', [$ujian, $b['kelas']]) }}" target="_blank" rel="noopener"
                                               class="{{ $tombol }} {{ $warnaHadir }}">Daftar hadir</a>
                                            @if ($b['ba'])
                                                <a href="{{ route('absensi.cetak.ba', [$ujian, $b['kelas']]) }}" target="_blank" rel="noopener"
                                                   class="{{ $tombol }} {{ $warnaBa }}">Berita acara</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-10 text-center text-sm text-gray-500">
                                        Ujian ini belum memiliki kelas peserta.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tombol kembali --}}
            <div>
                <a href="{{ $kembali }}" class="{{ $tombolKembali }}">&larr; Kembali</a>
            </div>
        </div>
    </div>

    {{-- SweetAlert2. Hapus baris <script src> ini kalau layout sudah memuatnya. --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <script>
        // Warna disamakan dengan tombol di halaman (Tailwind blue-600 / gray-500)
        const Notif = Swal.mixin({
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
            customClass: { popup: 'rounded-lg', confirmButton: 'text-sm', cancelButton: 'text-sm' },
        });

        @if (session('status'))
            Notif.fire({
                icon: 'success',
                title: 'Berhasil',
                text: @js(session('status')),
                timer: 2500,
                timerProgressBar: true,
                showConfirmButton: false,
            });
        @endif

        @if ($errors->any())
            Notif.fire({
                icon: 'error',
                title: 'Gagal',
                text: @js($errors->first()),
                confirmButtonText: 'Tutup',
            });
        @endif
    </script>
</x-app-layout>