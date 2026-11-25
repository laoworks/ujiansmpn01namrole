@php
    /*
    |--------------------------------------------------------------------------
    | Gaya bersama. Semua class ditulis utuh (jangan disambung) agar terbaca Tailwind.
    |--------------------------------------------------------------------------
    */

    // Kartu, judul, dan teks bantuan
    $kartu      = 'bg-white shadow-sm sm:rounded-lg p-6';
    $judulKartu = 'text-base font-semibold text-gray-900';
    $bantuan    = 'text-xs text-gray-500';

    // Kotak peringatan (garis di kiri)
    $alertDasar   = 'p-4 rounded-md border-l-4 text-sm';
    $alertKuning  = $alertDasar . ' bg-amber-50 border-amber-400 text-amber-900';
    $alertMerah   = $alertDasar . ' bg-red-50 border-red-500 text-red-800';

    // Badge status
    $badge = 'inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full';
    $warna = [
        'menunggu' => 'bg-blue-100 text-blue-700',
        'terkirim' => 'bg-green-100 text-green-700',
        'gagal'    => 'bg-red-100 text-red-700',
        'dilewati' => 'bg-gray-200 text-gray-700',
    ];

    // Tombol: px-4 py-2, rounded-md, text-sm, teks putih
    $tombol = 'inline-flex items-center justify-center px-4 py-2 text-white rounded-md text-sm font-medium transition '
            . 'focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-400 '
            . 'disabled:opacity-50 disabled:cursor-not-allowed';

    $tombolBiru   = $tombol . ' bg-blue-600 hover:bg-blue-700';
    $tombolHijau  = $tombol . ' bg-green-600 hover:bg-green-700';
    $tombolOranye = $tombol . ' bg-orange-500 hover:bg-orange-600';
    $tombolAbu    = $tombol . ' bg-gray-800 hover:bg-gray-700';

    // Tombol kecil di dalam tabel
    $tombolKecil = 'inline-flex items-center justify-center px-3 py-1 text-white rounded-md text-xs font-medium transition '
                 . 'focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-400 '
                 . 'disabled:opacity-50 disabled:cursor-not-allowed';

    // Isian formulir
    $isian = 'block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500';

    // Ringkasan status (kotak angka)
    $ringkasan = [
        ['label' => 'Menunggu',            'nilai' => $hitung->get('menunggu', 0), 'kotak' => 'bg-blue-50 border-blue-100',   'angka' => 'text-blue-700'],
        ['label' => 'Terkirim',            'nilai' => $hitung->get('terkirim', 0), 'kotak' => 'bg-green-50 border-green-100', 'angka' => 'text-green-700'],
        ['label' => 'Gagal',               'nilai' => $hitung->get('gagal', 0),    'kotak' => 'bg-red-50 border-red-100',     'angka' => 'text-red-700'],
        ['label' => 'Dilewati (tanpa nomor)', 'nilai' => $hitung->get('dilewati', 0), 'kotak' => 'bg-gray-50 border-gray-200', 'angka' => 'text-gray-700'],
        ['label' => 'Belum diantrikan',    'nilai' => $belumAntri,                 'kotak' => 'bg-gray-50 border-gray-200',   'angka' => 'text-gray-700'],
    ];

    $terakhir = $pengirimTerakhir ? \Illuminate\Support\Carbon::createFromTimestamp($pengirimTerakhir) : null;
    $pengirimMati = ! $terakhir || $terakhir->lt(now()->subMinutes(3));
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Hasil via WhatsApp</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Notifikasi sukses/error ditangani SweetAlert (lihat skrip di bawah) --}}

            {{-- Peringatan sistem --}}
            @if (config('ujian.wa.driver') !== 'fonnte')
                <div class="{{ $alertKuning }}">
                    <strong>Mode percobaan.</strong> Pesan tidak dikirim ke WhatsApp, hanya ditulis ke
                    <code class="px-1 rounded bg-amber-100">storage/logs/wa-percobaan.log</code>.
                    Ubah <code class="px-1 rounded bg-amber-100">WA_DRIVER=fonnte</code> di
                    <code class="px-1 rounded bg-amber-100">.env</code> untuk pengiriman sungguhan.
                </div>
            @endif

            @if ($pengirimMati)
                <div class="{{ $alertMerah }}">
                    <strong>Pengirim otomatis tidak berjalan.</strong>
                    Pesan dengan waktu "setelah ujian ditutup", pesan yang gagal, dan percobaan ulang tidak akan diproses.
                    Jalankan <code class="px-1 rounded bg-red-100">php artisan schedule:work</code> atau periksa tugas
                    "Ujian Scheduler" di Windows.
                </div>
            @else
                <p class="{{ $bantuan }} flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                    Pengirim otomatis aktif (terakhir berjalan {{ $terakhir->diffForHumans() }}).
                </p>
            @endif

            {{-- Pengaturan --}}
            <div class="{{ $kartu }}">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="{{ $judulKartu }}">{{ $ujian->judul }}</h3>
                    <span class="{{ $badge }} bg-blue-100 text-blue-700">{{ $ujian->fase }}</span>
                </div>
                <p class="mt-1 text-sm text-gray-500">{{ $ujian->mapel->nama }}</p>

                <form method="POST" action="{{ route('guru.wa.pengaturan', $ujian) }}" class="mt-5 space-y-5"
                      data-konfirmasi="Simpan pengaturan pengiriman WhatsApp?">
                    @csrf
                    @method('PATCH')

                    <label class="inline-flex items-center cursor-pointer">
                        <input type="hidden" name="kirim_wa" value="0">
                        <input type="checkbox" name="kirim_wa" value="1"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               @checked(old('kirim_wa', $ujian->kirim_wa))>
                        <span class="ml-2 text-sm text-gray-800 font-medium">Kirim hasil ujian ke WhatsApp siswa</span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="wa_waktu" value="Kapan dikirim" />
                            <select id="wa_waktu" name="wa_waktu" class="{{ $isian }}">
                                <option value="setelah_tutup" @selected(old('wa_waktu', $ujian->wa_waktu) === 'setelah_tutup')>
                                    Setelah ujian ditutup (disarankan)
                                </option>
                                <option value="segera" @selected(old('wa_waktu', $ujian->wa_waktu) === 'segera')>
                                    Segera setelah siswa selesai
                                </option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="wa_detail" value="Seberapa rinci" />
                            <select id="wa_detail" name="wa_detail" class="{{ $isian }}">
                                <option value="ringkas" @selected(old('wa_detail', $ujian->wa_detail) === 'ringkas')>
                                    Ringkas: nilai dan skor
                                </option>
                                <option value="rinci" @selected(old('wa_detail', $ujian->wa_detail) === 'rinci')>
                                    Rinci: + soal benar, salah, dan jawaban siswa
                                </option>
                                <option value="rinci_kunci" @selected(old('wa_detail', $ujian->wa_detail) === 'rinci_kunci')>
                                    Rinci + kunci jawaban (hanya setelah ujian ditutup)
                                </option>
                            </select>
                        </div>
                    </div>

                    <p class="{{ $bantuan }}">
                        Pada mode "setelah ditutup", ujian otomatis dianggap tutup saat jadwal ditambah durasi berakhir.
                        Kalau Anda menyalakan pengiriman setelah ada siswa yang selesai, hasil mereka ikut dikirim.
                    </p>

                    <button type="submit" class="{{ $tombolBiru }}">Simpan pengaturan</button>
                </form>
            </div>

            {{-- Ringkasan status --}}
            <div class="{{ $kartu }}">
                <h4 class="{{ $judulKartu }}">Ringkasan pengiriman</h4>

                <div class="mt-4 grid grid-cols-2 sm:grid-cols-5 gap-3 text-center">
                    @foreach ($ringkasan as $r)
                        <div class="p-3 rounded-md border {{ $r['kotak'] }} {{ $loop->last ? 'col-span-2 sm:col-span-1' : '' }}">
                            <div class="text-2xl font-bold {{ $r['angka'] }}">{{ $r['nilai'] }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ $r['label'] }}</div>
                        </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('guru.wa.ulang', $ujian) }}" class="mt-5"
                      data-konfirmasi="Antrikan ulang semua pesan yang gagal atau dilewati?">
                    @csrf
                    <button type="submit" class="{{ $tombolOranye }}">Antrikan ulang yang gagal / dilewati</button>
                </form>
            </div>

            {{-- Pesan uji --}}
            <div class="{{ $kartu }}">
                <h4 class="{{ $judulKartu }}">Kirim pesan uji</h4>
                <p class="mt-1 {{ $bantuan }}">Kirim satu pesan singkat ke nomor Anda sendiri untuk memastikan pengiriman berfungsi.</p>

                <form method="POST" action="{{ route('guru.wa.uji', $ujian) }}" class="mt-4 flex flex-wrap items-center gap-3"
                      data-konfirmasi="Kirim pesan uji ke nomor ini?">
                    @csrf
                    <x-text-input name="nomor" type="text" placeholder="081234567890" :value="old('nomor')" required />
                    <button type="submit" class="{{ $tombolHijau }}">Kirim pesan uji</button>
                </form>
                <x-input-error :messages="$errors->get('uji')" class="mt-2" />
            </div>

            {{-- Pratinjau --}}
            @if ($pratinjau)
                <div class="{{ $kartu }}">
                    <h4 class="{{ $judulKartu }}">Pratinjau pesan</h4>
                    <p class="mt-1 {{ $bantuan }}">Contoh dari satu siswa.</p>
                    <pre class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-md text-sm text-gray-800 whitespace-pre-wrap">{{ $pratinjau }}</pre>
                </div>
            @endif

            {{-- Daftar pesan --}}
            <div class="{{ $kartu }}">
                <h4 class="{{ $judulKartu }}">Daftar pesan</h4>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-600">
                                <th class="px-3 py-2">Siswa</th>
                                <th class="px-3 py-2">Kelas</th>
                                <th class="px-3 py-2">Nomor</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2">Keterangan</th>
                                <th class="px-3 py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($pesan as $p)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-3 font-medium text-gray-900">{{ $p->user->name }}</td>
                                    <td class="px-3 py-3 text-gray-700">{{ $p->user->kelas?->nama ?? '-' }}</td>
                                    <td class="px-3 py-3 font-mono text-xs text-gray-700">{{ \App\Support\NomorWa::samarkan($p->nomor) }}</td>
                                    <td class="px-3 py-3">
                                        <span class="{{ $badge }} {{ $warna[$p->status] ?? 'bg-gray-200 text-gray-700' }}">{{ ucfirst($p->status) }}</span>
                                        @if ($p->status === 'terkirim')
                                            <div class="mt-1 text-xs text-gray-500">{{ $p->dikirim_at?->format('d M H:i') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-xs text-gray-600">
                                        {{ $p->kesalahan }}
                                        @if ($p->status === 'menunggu' && $p->percobaan > 0)
                                            <div>Percobaan {{ $p->percobaan }}, coba lagi {{ $p->coba_lagi_at?->format('H:i') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right whitespace-nowrap">
                                        <form method="POST" action="{{ route('guru.wa.ulang', $ujian) }}" class="inline"
                                              data-konfirmasi="Kirim ulang pesan ke {{ $p->user->name }}?">
                                            @csrf
                                            <input type="hidden" name="pesan_id" value="{{ $p->id }}">
                                            <button type="submit" class="{{ $tombolKecil }} bg-blue-600 hover:bg-blue-700">Kirim ulang</button>
                                        </form>
                                        @if ($p->nomor && strlen($p->isi) < 1800)
                                            <a href="https://wa.me/{{ $p->nomor }}?text={{ rawurlencode($p->isi) }}" target="_blank" rel="noopener"
                                               class="{{ $tombolKecil }} ml-2 bg-green-600 hover:bg-green-700">Buka WhatsApp</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-10 text-center text-sm text-gray-500">
                                        Belum ada pesan. Pesan muncul setelah siswa selesai dan pengiriman dinyalakan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="mt-4 {{ $bantuan }}">
                    "Buka WhatsApp" membuka obrolan dengan isi pesan terisi, lalu Anda menekan kirim sendiri.
                    Ini cadangan kalau pengiriman otomatis bermasalah.
                </p>
            </div>

            <div>
                <a href="{{ route('guru.ujian.show', $ujian) }}" class="{{ $tombolAbu }}">&larr; Kembali ke detail ujian</a>
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

        // Notifikasi dari session / validasi
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

        // Dialog konfirmasi untuk form ber-atribut data-konfirmasi
        document.querySelectorAll('form[data-konfirmasi]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                Notif.fire({
                    icon: 'question',
                    title: 'Konfirmasi',
                    text: form.dataset.konfirmasi,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, lanjutkan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then(function (hasil) {
                    if (! hasil.isConfirmed) return;

                    // Cegah klik ganda selama halaman diproses
                    form.querySelectorAll('button[type="submit"]').forEach(function (b) { b.disabled = true; });
                    form.submit(); // submit() langsung tidak memicu listener lagi
                });
            });
        });
    </script>
</x-app-layout>