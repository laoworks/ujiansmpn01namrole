@php
    // Class tombol ditulis utuh supaya terbaca Tailwind (jangan disambung).
    // Satu warna aksi (biru) untuk semua tombol utama; hijau hanya untuk status.
    $tombol = 'inline-flex items-center justify-center gap-2 h-12 px-4 rounded-xl text-[15px] font-semibold transition active:scale-[0.98] active:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2';
    $isi = 'bg-blue-700 text-white shadow-sm hover:bg-blue-800';
    $garis = 'bg-white text-gray-800 border border-gray-300 hover:bg-gray-50';

    // Kartu putih bersudut besar dengan jarak dari tepi layar
    $blok = 'bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden';

    // Pesan error token/ttd dari semua ujian, ditampilkan lewat SweetAlert
    $pesanError = null;
    foreach ($ujian as $uu) {
        $b = $errors->getBag('ujian'.$uu->id);
        if ($b->has('token')) { $pesanError = ['Kode tidak diterima', $b->first('token')]; break; }
        if ($b->has('ttd')) { $pesanError = ['Tanda tangan belum tersimpan', $b->first('ttd')]; break; }
    }

    // Sembunyikan ujian yang terlewat (belum dikerjakan dan waktunya sudah habis).
    // Ujian yang sudah punya sesi tetap tampil, dan yang selesai masuk ke Riwayat.
    $ujian = $ujian->filter(fn ($u) => $sesi->get($u->id) || now()->lte($u->selesai_at))->values();

    // Ikon tombol (outline, ukuran sama)
    $ikon = 'w-5 h-5 shrink-0';

    $namaSiswa = auth()->user()->name;
    $inisial = mb_strtoupper(mb_substr($namaSiswa, 0, 1));
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ujian</h2>
    </x-slot>

    <div class="py-3 sm:py-8 overflow-x-hidden">
        <div class="w-full max-w-xl mx-auto px-3 sm:px-6 space-y-3 sm:space-y-4 break-words">

            {{-- Sapaan --}}
            <div class="rounded-2xl bg-blue-700 text-white shadow-sm px-4 py-5 flex items-center gap-3">
                <div class="shrink-0 w-12 h-12 rounded-full bg-white/15 grid place-items-center text-xl font-semibold">
                    {{ $inisial }}
                </div>
                <div class="min-w-0">
                    <p class="text-lg font-semibold leading-tight truncate">{{ $namaSiswa }}</p>
                    @if (auth()->user()->kelas)
                        <p class="text-sm text-blue-100">Kelas {{ auth()->user()->kelas->nama }}</p>
                    @endif
                </div>
            </div>

            @unless (auth()->user()->kelas_id)
                <div class="px-4 py-3 text-sm bg-amber-50 text-amber-900 rounded-2xl ring-1 ring-amber-200">
                    Akun belum masuk kelas. Hubungi admin.
                </div>
            @endunless

            @forelse ($ujian as $u)
                @php
                    $s = $sesi->get($u->id);
                    $berlangsung = $s && $s->status === 'berlangsung';
                    $lanjut = $lanjutIds->contains($u->id);
                    $belum = ! $s && now()->lt($u->mulai_at);
                    $bisaMulai = ! $s && ! $belum;
                    $sudahTtd = filled($ttd->get($u->id));
                    $bolehToken = $berlangsung || $sudahTtd || ! $wajibTtd;
                    $hariSama = $u->mulai_at->isSameDay($u->selesai_at);
                @endphp

                <section class="{{ $blok }}">
                    {{-- Judul, status, dan info singkat --}}
                    <div class="px-4 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h4 class="text-lg font-semibold text-gray-900 leading-snug">{{ $u->judul }}</h4>
                                <p class="text-sm text-gray-500">{{ $u->mapel?->nama ?? '-' }}</p>
                            </div>

                            <span class="shrink-0 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium whitespace-nowrap
                                {{ $belum ? 'bg-blue-50 text-blue-700' : 'bg-green-50 text-green-700' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $belum ? 'bg-blue-500' : 'bg-green-500' }}"></span>
                                @if ($berlangsung) Berlangsung
                                @elseif ($belum) Belum buka
                                @else Dibuka
                                @endif
                            </span>
                        </div>

                        <p class="mt-3 text-sm font-medium text-gray-800">{{ $u->soal_count }} soal, {{ $u->durasi_menit }} menit</p>
                        <p class="text-sm text-gray-500">
                            @if ($hariSama)
                                {{ $u->mulai_at->format('d M, H:i') }} sampai {{ $u->selesai_at->format('H:i') }}
                            @else
                                {{ $u->mulai_at->format('d M H:i') }} sampai {{ $u->selesai_at->format('d M H:i') }}
                            @endif
                        </p>
                    </div>

                    {{-- Tanda tangan kehadiran --}}
                    @if (! $s)
                        @if ($sudahTtd)
                            {{-- Sudah tanda tangan: ringkas, bisa diganti --}}
                            <details class="group border-t border-gray-100">
                                <summary class="flex items-center justify-between gap-3 px-4 min-h-[60px] cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                    <span class="flex items-center gap-3 min-w-0">
                                        <img src="{{ $ttd->get($u->id) }}" alt="Tanda tangan"
                                             class="h-9 w-auto max-w-[80px] bg-white border border-gray-200 rounded">
                                        <span class="text-sm text-gray-700">Tersimpan</span>
                                    </span>
                                    <span class="shrink-0 inline-flex items-center h-9 px-3 rounded-lg border border-gray-300 text-sm font-medium text-gray-800 group-open:bg-gray-100">
                                        Ganti tanda tangan
                                    </span>
                                </summary>

                                {{-- Canvas di x-ttd-pad harus w-full agar tidak melebar dari layar --}}
                                <form method="POST" action="{{ route('siswa.ttd.simpan', $u) }}"
                                      class="ttd-form px-4 pb-4 w-full max-w-full overflow-hidden">
                                    @csrf
                                    <x-ttd-pad name="ttd">
                                        <button type="submit" x-bind:disabled="kosong" class="{{ $tombol }} {{ $isi }}">
                                            Simpan
                                        </button>
                                    </x-ttd-pad>
                                </form>
                            </details>
                        @else
                            {{-- Belum tanda tangan: langsung terbuka dan jelas langkahnya --}}
                            <div class="border-t border-gray-100 bg-gray-50 px-4 py-4">
                                <p class="mb-3 font-medium text-gray-900">
                                    @if ($wajibTtd) 1. @endif Tanda tangan
                                </p>

                                <form method="POST" action="{{ route('siswa.ttd.simpan', $u) }}"
                                      class="ttd-form w-full max-w-full overflow-hidden">
                                    @csrf
                                    <x-ttd-pad name="ttd">
                                        <button type="submit" x-bind:disabled="kosong" class="{{ $tombol }} {{ $isi }}">
                                            Simpan
                                        </button>
                                    </x-ttd-pad>
                                </form>
                            </div>
                        @endif
                    @endif

                    {{-- Masuk ujian --}}
                    @if ($berlangsung && $lanjut)
                        <div class="px-4 py-4 border-t border-gray-100">
                            <a href="{{ route('siswa.ujian.kerjakan', $s) }}" class="w-full {{ $tombol }} {{ $isi }}">
                                Lanjutkan ujian
                            </a>
                        </div>
                    @elseif ($berlangsung || $bisaMulai)
                        <div class="px-4 py-4 border-t border-gray-100">
                            @if ($bolehToken)
                                <form method="POST" action="{{ route('siswa.ujian.mulai', $u) }}"
                                      class="space-y-3"
                                      data-judul="{{ $berlangsung ? 'Lanjutkan ujian?' : 'Mulai ujian?' }}"
                                      data-konfirmasi="{{ $berlangsung
                                          ? 'Waktu tetap berjalan.'
                                          : 'Waktu mulai dihitung sekarang.' }}"
                                      data-ikon="question"
                                      data-tombol="{{ $berlangsung ? 'Ya, lanjutkan' : 'Ya, mulai' }}">
                                    @csrf

                                    <div>
                                        <label for="token-{{ $u->id }}" class="block mb-2 font-medium text-gray-900">
                                            @if ($wajibTtd) 2. @endif Kode ujian
                                        </label>
                                        <x-text-input id="token-{{ $u->id }}" name="token" type="text" maxlength="20" autocomplete="off"
                                            autocapitalize="characters" autocorrect="off" spellcheck="false"
                                            placeholder="Contoh: K7F2QXAB" aria-label="Kode ujian"
                                            class="block w-full h-12 !rounded-xl uppercase tracking-[0.2em] font-mono text-base text-center placeholder:normal-case placeholder:tracking-normal placeholder:font-sans" required />
                                    </div>

                                    <div class="grid gap-2 sm:grid-cols-2">
                                        <button type="submit" class="w-full {{ $tombol }} {{ $isi }}">
                                            {{ $berlangsung ? 'Lanjutkan ujian' : 'Mulai ujian' }}
                                        </button>

                                        <button type="button" onclick="bukaPemindai()" class="w-full {{ $tombol }} {{ $garis }}">
                                            <svg class="{{ $ikon }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75V16.5zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
                                            </svg>
                                            Pindai QR
                                        </button>
                                    </div>
                                </form>
                            @else
                                <p class="font-medium text-gray-400">2. Kode ujian</p>
                            @endif
                        </div>
                    @endif
                </section>
            @empty
                <div class="{{ $blok }} px-4 py-8 text-center">
                    <p class="font-medium text-gray-900">Belum ada ujian</p>
                </div>
            @endforelse

            {{-- Riwayat --}}
            @if ($riwayat->contains(fn ($r) => $r->ujian))
                <h3 class="px-1 pt-2 text-sm font-semibold text-gray-600">Sudah dikerjakan</h3>

                <div class="{{ $blok }} divide-y divide-gray-100">
                    @foreach ($riwayat as $r)
                        @continue(! $r->ujian)
                        <div class="flex items-center gap-3 px-4 py-3">
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-gray-900 truncate">{{ $r->ujian->judul }}</div>
                                <div class="text-xs text-gray-500 truncate">
                                    {{ $r->ujian->mapel?->nama ?? '-' }}
                                    @if ($r->selesai_at)
                                        , {{ $r->selesai_at->format('d M, H:i') }}
                                    @endif
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="text-[11px] text-gray-500 leading-none">Nilai</div>
                                <div class="mt-1 text-base font-semibold text-gray-900 tabular-nums leading-none">
                                    {{ $r->ujian->tampil_nilai && $r->nilai !== null ? number_format($r->nilai, 2) : '-' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Dipakai aplikasi Android dan pemindai: masukDenganQr('UJIAN:12:K7F2QXAB') --}}
    <form id="form-qr" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="token">
    </form>

    {{-- Pemindai QR: lembar bawah di HP, kotak tengah di layar lebar --}}
    <div id="modal-pindai" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center bg-black/70 sm:p-4">
        <div class="w-full sm:max-w-sm bg-white rounded-t-3xl sm:rounded-2xl p-4 pb-[max(1.5rem,env(safe-area-inset-bottom))] max-h-[90vh] overflow-y-auto">
            <div class="sm:hidden mx-auto mb-3 h-1 w-10 rounded-full bg-gray-300" aria-hidden="true"></div>
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-lg font-semibold text-gray-900">Pindai QR</h4>
                <button type="button" onclick="tutupPemindai()" aria-label="Tutup"
                        class="w-10 h-10 -mr-2 grid place-items-center rounded-full text-gray-500 text-2xl leading-none active:bg-gray-100">&times;</button>
            </div>

            <div id="area-pindai" class="w-full overflow-hidden rounded-2xl bg-gray-100 min-h-[240px]"></div>

            {{-- Status kamera atau penyebab bila kamera gagal --}}
            <p id="info-pindai" class="mt-3 text-sm text-center text-gray-600">Membuka kamera</p>

            <div class="mt-4 space-y-2">
                {{-- Muncul hanya saat kamera gagal --}}
                <button type="button" id="btn-ulang" onclick="mulaiKamera()" class="hidden w-full {{ $tombol }} {{ $isi }}">
                    <svg class="{{ $ikon }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    Coba lagi
                </button>

                <label class="w-full cursor-pointer {{ $tombol }} {{ $garis }}">
                    <svg class="{{ $ikon }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                    </svg>
                    Ambil foto QR
                    <input type="file" id="berkas-qr" accept="image/*" capture="environment" class="hidden">
                </label>

                <button type="button" onclick="tutupPemindai()" class="w-full {{ $tombol }} bg-transparent text-gray-600 hover:bg-gray-100">
                    Tutup
                </button>
            </div>
            <div id="area-berkas" class="hidden"></div>
        </div>
    </div>

    {{-- Tombol ulang/hapus di dalam kotak tanda tangan (dibuat komponen x-ttd-pad) --}}
    <style>
        .ttd-form button[type="button"] {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            min-height: 3rem;
            padding: 0 1rem;
            border-radius: .75rem !important;
            border: 1px solid #fcd34d !important;
            background: #fffbeb !important;
            color: #92400e !important;
            font-size: 15px;
            font-weight: 500;
            transition: background-color .15s;
        }
        .ttd-form button[type="button"]:hover { background: #fef3c7 !important; }
        .ttd-form button[type="button"]:active { background: #fde68a !important; }
        .ttd-form button[type="button"]:focus-visible { outline: 2px solid #d97706; outline-offset: 2px; }
        .ttd-form button[type="button"]:disabled { opacity: .5; cursor: not-allowed; }
    </style>

    {{-- Pustaka pemindai: salin html5-qrcode.min.js ke public/js/ --}}
    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>

    <script>
        const URL_MULAI = @js(route('siswa.ujian.mulai', ['ujian' => '__ID__']));

        // Mengurai teks QR "UJIAN:12:K7F2QXAB". Mengembalikan null bila bukan QR ujian.
        function uraiQr(teks) {
            const m = /^UJIAN:(\d+):([A-Z0-9]+)$/i.exec(String(teks).trim());
            return m ? { id: m[1], kode: m[2] } : null;
        }

        // Juga dipanggil aplikasi Android setelah memindai: masukDenganQr('UJIAN:12:K7F2QXAB')
        window.masukDenganQr = function (teks) {
            const q = uraiQr(teks);
            if (!q) {
                Swal.fire({ icon: 'error', title: 'QR tidak dikenali' });
                return false;
            }
            const f = document.getElementById('form-qr');
            f.action = URL_MULAI.replace('__ID__', q.id);
            f.token.value = q.kode;
            f.submit();
            return true;
        };

        const $id = (id) => document.getElementById(id);
        let pemindai = null;
        let sedangMemproses = false;

        function info(teks, galat) {
            const e = $id('info-pindai');
            e.textContent = teks;
            e.className = 'mt-3 text-sm text-center ' + (galat ? 'text-red-700' : 'text-gray-600');
        }

        function tampilUlang(tampil) {
            $id('btn-ulang').classList.toggle('hidden', !tampil);
        }

        // Mengubah error kamera (Error, DOMException, atau string dari html5-qrcode) menjadi pesan yang jelas
        function pesanGalat(e) {
            const gabung = ((e && e.name) || '') + ' ' + String((e && e.message) ? e.message : e || '');

            if (/NotAllowed|Permission|denied/i.test(gabung)) return 'Izin kamera ditolak. Aktifkan di pengaturan browser, lalu muat ulang.';
            if (/NotFound|DevicesNotFound|Requested device not found/i.test(gabung)) return 'Kamera tidak ditemukan.';
            if (/NotReadable|TrackStart|Could not start video source|in use/i.test(gabung)) return 'Kamera sedang dipakai aplikasi lain.';
            if (/Security|insecure|https/i.test(gabung)) return 'Kamera diblokir. Butuh HTTPS.';
            if (/Overconstrained/i.test(gabung)) return 'Kamera tidak cocok.';
            return 'Kamera tidak bisa dibuka.';
        }

        async function statusIzinKamera() {
            try {
                const r = await navigator.permissions.query({ name: 'camera' });
                return r.state; // 'granted' | 'prompt' | 'denied'
            } catch (e) {
                return null; // browser tidak mendukung pengecekan ini
            }
        }

        // Dipanggil tombol "Pindai QR"
        window.bukaPemindai = async function () {
            // Aplikasi Android: pakai pemindai bawaan aplikasi (kontrak ke aplikasi Flutter/Android)
            if (window.PindaiQR && typeof window.PindaiQR.postMessage === 'function') {
                window.PindaiQR.postMessage('buka');
                return;
            }
            if (window.Android && typeof window.Android.scanQr === 'function') {
                window.Android.scanQr();
                return;
            }

            const m = $id('modal-pindai');
            m.classList.remove('hidden');
            m.classList.add('flex');

            await window.mulaiKamera();
        };

        window.mulaiKamera = async function () {
            tampilUlang(false);
            info('Membuka kamera', false);
            sedangMemproses = false;

            // 1) Kamera hanya boleh di HTTPS yang tepercaya (atau localhost)
            if (!window.isSecureContext) {
                info('Kamera butuh HTTPS. Pakai foto QR atau ketik kode.', true);
                return;
            }

            // 2) Pustaka pemindai harus termuat
            if (typeof Html5Qrcode === 'undefined') {
                info('Pemindai tidak termuat. Pakai foto QR atau ketik kode.', true);
                return;
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                info('Kamera tidak didukung. Pakai foto QR atau ketik kode.', true);
                return;
            }

            // 3) Izin yang sudah diblokir tidak akan memunculkan pertanyaan lagi
            if ((await statusIzinKamera()) === 'denied') {
                info('Izin kamera diblokir. Aktifkan di pengaturan browser.', true);
                tampilUlang(true);
                return;
            }

            // Bersihkan sisa pemindai sebelumnya
            if (pemindai) { try { await pemindai.stop(); } catch (e) {} try { pemindai.clear(); } catch (e) {} pemindai = null; }

            pemindai = new Html5Qrcode('area-pindai', {
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                experimentalFeatures: { useBarCodeDetectorIfSupported: true },
                verbose: false,
            });

            const konfig = {
                fps: 10,
                qrbox: (w, h) => { const s = Math.floor(Math.min(w, h) * 0.7); return { width: s, height: s }; },
            };

            // Kamera belakang dulu; bila tidak ada (mis. laptop) pakai kamera yang tersedia
            let galat = null;
            for (const kamera of [{ facingMode: 'environment' }, { facingMode: 'user' }]) {
                try {
                    await pemindai.start(kamera, konfig, saatTerbaca, () => {});
                    info('Arahkan ke QR.', false);
                    return;
                } catch (e) {
                    galat = e;
                    if (!/Overconstrained|NotFound|DevicesNotFound/i.test((e && e.name) + ' ' + e)) break;
                }
            }

            try { pemindai.clear(); } catch (e) {}
            pemindai = null;
            info(pesanGalat(galat), true);
            tampilUlang(true);
        };

        async function saatTerbaca(teks) {
            if (sedangMemproses || !pemindai) return;

            if (!uraiQr(teks)) {
                info('Bukan QR ujian.', true);
                return;
            }

            sedangMemproses = true;
            await window.tutupPemindai();
            window.masukDenganQr(teks);
        }

        window.tutupPemindai = async function () {
            const p = pemindai;
            pemindai = null;
            if (p) { try { await p.stop(); } catch (e) {} try { p.clear(); } catch (e) {} }

            const m = $id('modal-pindai');
            m.classList.add('hidden');
            m.classList.remove('flex');
        };

        // Cadangan: baca QR dari foto (tidak butuh izin kamera langsung)
        $id('berkas-qr').addEventListener('change', async (e) => {
            const berkas = e.target.files && e.target.files[0];
            e.target.value = '';
            if (!berkas) return;

            if (typeof Html5Qrcode === 'undefined') {
                Swal.fire({ icon: 'error', title: 'Pemindai tidak termuat' });
                return;
            }

            try {
                const p = new Html5Qrcode('area-berkas');
                const teks = await p.scanFile(berkas, false);
                try { p.clear(); } catch (x) {}

                if (!uraiQr(teks)) {
                    info('Bukan QR ujian.', true);
                    return;
                }
                await window.tutupPemindai();
                window.masukDenganQr(teks);
            } catch (err) {
                info('QR tidak terbaca. Foto ulang.', true);
            }
        });
    </script>

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

            @if ($pesanError)
                Swal.fire({
                    icon: 'error',
                    title: @js($pesanError[0]),
                    text: @js($pesanError[1]),
                    confirmButtonColor: '#1d4ed8',
                });
            @endif
        });
    </script>
</x-app-layout>