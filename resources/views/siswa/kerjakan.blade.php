<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $ujian->judul }}</title>
    <style>
        [x-cloak] { display: none !important; }

        :root {
            --latar: #f1f3f7;
            --kertas: #ffffff;
            --tinta: #172033;
            --tinta-pudar: #5d6679;
            --garis: #dde2ec;
            --garis-tegas: #b3bdd1;
            --utama: #1d4ed8;
            --utama-tua: #1e40af;
            --utama-muda: #e9effd;
            --ragu: #d99a0b;
            --ragu-muda: #fff4d6;
            --ragu-teks: #7a5300;
            --bahaya: #c0352b;
            --bahaya-muda: #fdeceb;
            --sukses: #1b8a5a;
        }

        html, body { max-width: 100%; overflow-x: hidden; }
        body {
            background: var(--latar);
            color: var(--tinta);
            font-family: "Segoe UI", system-ui, -apple-system, Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.5;
            -webkit-tap-highlight-color: transparent;
        }

        .ujian-aman { user-select: none; -webkit-user-select: none; -webkit-touch-callout: none; }
        .teks-bungkus { overflow-wrap: anywhere; word-break: break-word; }
        .teks-bungkus img { max-width: 100%; height: auto; }
        .waktu { font-variant-numeric: tabular-nums; }

        /* Teks soal berserif agar terasa seperti lembar ujian cetak */
        .teks-soal {
            font-family: Charter, "Iowan Old Style", "Palatino Linotype", Georgia, serif;
            font-size: 1.0625rem;
            line-height: 1.7;
            max-width: 68ch;
        }
        @media (min-width: 640px) { .teks-soal { font-size: 1.125rem; } }

        /* Pilihan jawaban: bulat = pilih satu, kotak = pilih lebih dari satu */
        .opsi {
            position: relative;
            display: grid;
            grid-template-columns: 2.25rem 1fr;
            gap: .75rem;
            align-items: start;
            min-height: 3.25rem;
            padding: .75rem;
            border: 1.5px solid var(--garis);
            border-radius: .75rem;
            background: var(--kertas);
            cursor: pointer;
            transition: border-color .12s, background-color .12s;
        }
        .opsi:active { background: var(--latar); }
        .opsi.aktif { border-color: var(--utama); background: var(--utama-muda); }
        .opsi input { position: absolute; opacity: 0; pointer-events: none; }
        .opsi:focus-within { outline: 2px solid var(--utama); outline-offset: 2px; }
        .opsi.mati { opacity: .45; cursor: not-allowed; }

        .huruf {
            width: 2.25rem; height: 2.25rem;
            display: grid; place-items: center;
            border-radius: 999px;
            border: 1.5px solid var(--garis-tegas);
            background: var(--kertas);
            font-weight: 600; font-size: .95rem;
            transition: background-color .12s, color .12s, border-color .12s;
        }
        .huruf.kotak-centang { border-radius: .45rem; }
        .opsi.aktif .huruf { background: var(--utama); border-color: var(--utama); color: #fff; }

        /* Benar / Salah */
        .pernyataan {
            border: 1.5px solid var(--garis);
            border-radius: .75rem;
            background: var(--kertas);
            padding: .75rem;
        }
        .seg { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; margin-top: .75rem; }
        .seg button {
            min-height: 2.75rem;
            border-radius: .6rem;
            border: 1.5px solid var(--garis);
            background: var(--kertas);
            font-weight: 600; font-size: .95rem;
            transition: background-color .12s, border-color .12s, color .12s;
        }
        .seg button:active { background: var(--latar); }
        .seg button:focus-visible { outline: 2px solid var(--utama); outline-offset: 2px; }
        .seg button.benar { background: var(--sukses); border-color: var(--sukses); color: #fff; }
        .seg button.salah { background: var(--bahaya); border-color: var(--bahaya); color: #fff; }

        /* Tombol */
        .tombol {
            display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
            height: 2.9rem;
            padding: 0 1rem;
            border-radius: .65rem;
            font-weight: 600; font-size: .95rem;
            border: 1.5px solid transparent;
            transition: background-color .12s, border-color .12s, opacity .12s;
        }
        .tombol:active:not(:disabled) { opacity: .8; }
        .tombol:focus-visible { outline: 2px solid var(--utama); outline-offset: 2px; }
        .tombol:disabled { opacity: .35; cursor: not-allowed; }
        .t-utama { background: var(--utama); color: #fff; }
        .t-utama:hover:not(:disabled) { background: var(--utama-tua); }
        .t-garis { background: var(--kertas); border-color: var(--garis-tegas); color: var(--tinta); }
        .t-ragu { background: var(--kertas); border-color: var(--ragu); color: var(--ragu-teks); }
        .t-ragu.aktif { background: var(--ragu); border-color: var(--ragu); color: #fff; }
        .t-gelap { background: var(--tinta); color: #fff; }

        /* Nomor soal */
        .nomor {
            height: 2.75rem;
            border-radius: .6rem;
            border: 1.5px solid var(--garis);
            background: var(--kertas);
            font-weight: 600; font-size: .9rem;
            font-variant-numeric: tabular-nums;
        }
        .nomor.sudah { background: var(--utama); border-color: var(--utama); color: #fff; }
        .nomor.ragu { background: var(--ragu-muda); border-color: var(--ragu); color: var(--ragu-teks); }
        .nomor.sekarang { box-shadow: 0 0 0 2px var(--kertas), 0 0 0 4px var(--tinta); }
        .nomor:focus-visible { outline: 2px solid var(--utama); outline-offset: 2px; }

        /* Bilah progres */
        .progres { height: 3px; background: var(--garis); }
        .progres > div { height: 100%; background: var(--utama); transition: width .3s ease; }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
    function antiCurang(init) {
        const csrf = () => document.querySelector('meta[name=csrf-token]').content;

        return {
            pel: { jumlah: init.pelanggaran, batas: init.batas },
            layar: '',            // '' | 'mulai' | 'peringatan'
            pesanPel: '',
            episode: false,
            menutup: false,
            antri: [],
            terakhir: {},
            bisaFullscreen: !!(document.fullscreenEnabled && document.documentElement.requestFullscreen),

            jagaUjian() {
                if (this.bisaFullscreen && !document.fullscreenElement) this.layar = 'mulai';

                // Di APK, "pergi dari aplikasi" dideteksi oleh Flutter (lifecycle paused).
                // visibilitychange dan blur di WebView tidak andal dan memicu pelanggaran palsu,
                // jadi keduanya hanya aktif di browser biasa.
                const diAplikasi = /UjianApp\//.test(navigator.userAgent);

                if (!diAplikasi) {
                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden) {
                            this.pelanggaran('keluar_halaman', 'Berpindah tab atau aplikasi');
                        } else if (this.pel.batas > 0 && this.pel.jumlah >= this.pel.batas) {
                            this.menutup = true;
                            window.location.href = init.urlSelesai;
                        }
                    });

                    // Pemeriksaan fokus ditunda sebentar agar dialog tidak memicu pelanggaran palsu
                    window.addEventListener('blur', () => {
                        setTimeout(() => {
                            if (!document.hasFocus()) this.pelanggaran('keluar_halaman', 'Jendela ujian kehilangan fokus');
                        }, 400);
                    });
                }

                document.addEventListener('fullscreenchange', () => {
                    if (!document.fullscreenElement && this.bisaFullscreen && !this.menutup) {
                        this.pelanggaran('keluar_layar_penuh', 'Keluar dari layar penuh');
                    }
                });

                ['copy', 'cut', 'paste'].forEach((n) => document.addEventListener(n, (e) => {
                    e.preventDefault();
                    this.catat('salin_tempel', 'Mencoba ' + (n === 'copy' ? 'menyalin' : n === 'cut' ? 'memotong' : 'menempel'));
                }));

                document.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    this.catat('klik_kanan', 'Membuka menu klik kanan');
                });

                ['selectstart', 'dragstart'].forEach((n) => document.addEventListener(n, (e) => e.preventDefault()));

                document.addEventListener('keydown', (e) => {
                    const k = (e.key || '').toLowerCase();
                    const ctrl = e.ctrlKey || e.metaKey;
                    const dilarang = e.key === 'F12'
                        || (ctrl && ['c', 'v', 'x', 'p', 's', 'u', 'a'].includes(k))
                        || (ctrl && e.shiftKey && ['i', 'j', 'c'].includes(k));
                    if (dilarang) {
                        e.preventDefault();
                        this.catat('pintasan', 'Menekan ' + (ctrl ? 'Ctrl+' : '') + (e.shiftKey ? 'Shift+' : '') + e.key);
                    }
                });

                document.addEventListener('keyup', (e) => {
                    if (e.key === 'PrintScreen') this.catat('screenshot', 'Menekan PrintScreen');
                });

                setInterval(() => this.kirimAntri(), 5000);
            },

            // Hanya dicatat (tidak dihitung dan tidak ada layar peringatan)
            catat(jenis, ket) {
                const t = Date.now();
                if (this.terakhir[jenis] && t - this.terakhir[jenis] < 5000) return;
                this.terakhir[jenis] = t;
                this.lapor(jenis, ket);
            },

            // Pelanggaran yang dihitung.
            // Jika batas tercapai: langsung dialihkan ke halaman selesai tanpa layar peringatan.
            // Jika belum: layar peringatan tampil dan harus ditutup siswa.
            async pelanggaran(jenis, ket) {
                if (this.menutup || this.episode) return;
                this.episode = true;
                this.pel.jumlah++;

                const pesan = 'Terdeteksi: ' + ket + '.';
                const capaiBatas = this.pel.batas > 0 && this.pel.jumlah >= this.pel.batas;

                if (!capaiBatas) {
                    this.layar = 'peringatan';
                    this.pesanPel = pesan;
                }

                const dialihkan = await this.lapor(jenis, ket);

                // Cadangan: batas tercapai tetapi laporan gagal/belum menutup sesi di server
                if (capaiBatas && !dialihkan) {
                    this.layar = 'peringatan';
                    this.pesanPel = pesan;
                }
            },

            // Mengembalikan true jika halaman dialihkan (ujian sudah berakhir di server)
            async lapor(jenis, ket) {
                try {
                    const r = await fetch(init.urlLapor, {
                        method: 'POST',
                        keepalive: true,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                        },
                        body: JSON.stringify({ jenis: jenis, keterangan: ket }),
                    });
                    if (r.status === 410) { this.menutup = true; window.location.href = init.urlSelesai; return true; }
                    if (r.status === 403) { this.menutup = true; window.location.href = init.urlDashboard; return true; }
                    if (!r.ok) throw new Error('gagal');
                    const d = await r.json();
                    this.pel.jumlah = d.jumlah;
                    if (d.selesai) { this.menutup = true; window.location.href = init.urlSelesai; return true; }
                    return false;
                } catch (e) {
                    if (this.antri.length < 50) this.antri.push({ jenis: jenis, ket: ket });
                    return false;
                }
            },

            kirimAntri() {
                if (this.antri.length === 0) return;
                this.antri.splice(0).forEach((x) => this.lapor(x.jenis, x.ket));
            },

            async masukLayarPenuh() {
                if (this.bisaFullscreen && !document.fullscreenElement) {
                    try { await document.documentElement.requestFullscreen(); } catch (e) {}
                }
            },

            async mulaiLayarPenuh() {
                await this.masukLayarPenuh();
                this.layar = '';
            },

            async lanjutkan() {
                await this.masukLayarPenuh();
                this.layar = '';
                setTimeout(() => { this.episode = false; }, 1000);
            },
        };
    }

    function halamanUjian(init) {
        const csrf = () => document.querySelector('meta[name=csrf-token]').content;
        const objek = (v) => (v && !Array.isArray(v) ? v : {});

        return {
            ...antiCurang(init),

            soal: init.soal || [],
            idx: 0,
            jawab: objek(init.jawab),
            ragu: objek(init.ragu),
            sisa: init.sisa,
            akhir: 0,
            waktu: '',
            status: 'tersimpan',   // 'tersimpan' | 'menyimpan' | 'gagal'
            tertunda: {},          // soal_id yang belum berhasil tersimpan
            panel: false,          // daftar nomor soal (laci di HP)

            get s() {
                return this.soal[this.idx] || {};
            },

            get jumlahTerjawab() {
                return this.soal.filter((q) => this.sudahDijawab(q)).length;
            },

            get persen() {
                return this.soal.length ? Math.round((this.jumlahTerjawab / this.soal.length) * 100) : 0;
            },

            mulai() {
                this.jagaUjian();
                this.akhir = Date.now() + this.sisa * 1000;
                this.tick();
                setInterval(() => this.tick(), 1000);
                setInterval(() => this.ulangiTertunda(), 5000);
            },

            tick() {
                this.sisa = Math.max(0, Math.round((this.akhir - Date.now()) / 1000));
                const j = Math.floor(this.sisa / 3600);
                const m = Math.floor((this.sisa % 3600) / 60);
                const d = this.sisa % 60;
                const p = (n) => String(n).padStart(2, '0');
                this.waktu = (j > 0 ? j + ':' : '') + p(m) + ':' + p(d);

                if (this.sisa <= 0 && !this.menutup) {
                    this.menutup = true;
                    document.getElementById('form-kirim').submit();
                }
            },

            sudahDijawab(q) {
                const j = this.jawab[q.id];
                if (q.tipe === 'pg') return !!j;
                if (q.tipe === 'pg_kompleks') return Array.isArray(j) && j.length > 0;
                if (q.tipe === 'benar_salah') return !!j && Object.keys(j).length > 0;
                return false;
            },

            kelasNav(i) {
                const q = this.soal[i];
                let k = '';
                if (this.ragu[q.id]) k = 'ragu';
                else if (this.sudahDijawab(q)) k = 'sudah';
                if (i === this.idx) k += ' sekarang';
                return k;
            },

            ke(i) {
                if (i >= 0 && i < this.soal.length) {
                    this.idx = i;
                    window.scrollTo({ top: 0 });
                }
            },

            pilihPg(kode) {
                this.jawab = { ...this.jawab, [this.s.id]: kode };
                this.simpan(this.s.id);
            },

            pilihKompleks(kode) {
                const id = this.s.id;
                let arr = Array.isArray(this.jawab[id]) ? [...this.jawab[id]] : [];
                if (arr.includes(kode)) arr = arr.filter((x) => x !== kode);
                else if (arr.length < 2) arr.push(kode);
                this.jawab = { ...this.jawab, [id]: arr };
                this.simpan(id);
            },

            pilihBs(kode, nilai) {
                const id = this.s.id;
                this.jawab = { ...this.jawab, [id]: { ...(this.jawab[id] || {}), [kode]: nilai } };
                this.simpan(id);
            },

            toggleRagu() {
                const id = this.s.id;
                this.ragu = { ...this.ragu, [id]: !this.ragu[id] };
                this.simpan(id);
            },

            async simpan(id) {
                this.tertunda[id] = true;
                this.status = 'menyimpan';
                try {
                    const r = await fetch(init.urlJawab, {
                        method: 'POST',
                        keepalive: true,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                        },
                        body: JSON.stringify({
                            soal_id: id,
                            jawaban: this.jawab[id] ?? null,
                            ragu: !!this.ragu[id],
                        }),
                    });
                    if (r.status === 410) { this.menutup = true; window.location.href = init.urlSelesai; return; }
                    if (r.status === 403) { this.menutup = true; window.location.href = init.urlDashboard; return; }
                    if (!r.ok) throw new Error('gagal');
                    delete this.tertunda[id];
                    this.status = Object.keys(this.tertunda).length ? 'gagal' : 'tersimpan';
                } catch (e) {
                    this.status = 'gagal';
                }
            },

            ulangiTertunda() {
                Object.keys(this.tertunda).forEach((id) => this.simpan(isNaN(id) ? id : Number(id)));
            },

            // Konfirmasi memakai SweetAlert (dengan cadangan confirm() bila Swal tidak termuat)
            kirim() {
                const kosong = this.soal.length - this.jumlahTerjawab;
                const pesan = kosong > 0
                    ? 'Masih ada ' + kosong + ' soal belum dijawab. Yakin ingin menyelesaikan ujian?'
                    : 'Yakin ingin menyelesaikan ujian dan mengirim jawaban?';

                const kirimSekarang = () => {
                    this.menutup = true;
                    document.getElementById('form-kirim').submit();
                };

                if (typeof Swal === 'undefined') {
                    if (confirm(pesan)) kirimSekarang();
                    return;
                }

                Swal.fire({
                    title: 'Selesaikan ujian?',
                    text: pesan,
                    icon: kosong > 0 ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, kirim',
                    cancelButtonText: 'Kembali',
                    confirmButtonColor: '#1d4ed8',
                    cancelButtonColor: '#5d6679',
                    reverseButtons: true,
                    allowOutsideClick: false,
                }).then((h) => {
                    if (h.isConfirmed) kirimSekarang();
                });
            },
        };
    }
    </script>
</head>

<body class="ujian-aman teks-bungkus overflow-x-hidden" x-data="halamanUjian(@js($init))" x-init="mulai()" x-cloak>

    {{-- Bilah atas --}}
    <header class="sticky top-0 z-20 bg-white border-b" style="border-color: var(--garis); padding-top: env(safe-area-inset-top, 0px);">
        <div class="max-w-6xl mx-auto px-4 sm:px-5 h-14 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="font-semibold leading-tight truncate">{{ $ujian->judul }}</div>
                <div class="text-xs truncate" style="color: var(--tinta-pudar);">{{ $ujian->mapel->nama }}</div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                {{-- Muncul hanya bila sudah pernah melanggar --}}
                <template x-if="pel.jumlah > 0 && pel.batas > 1">
                    <span class="text-xs font-semibold px-2 py-1 rounded-md whitespace-nowrap"
                          style="background: var(--bahaya-muda); color: var(--bahaya);">
                        <span x-text="pel.jumlah"></span>/<span x-text="pel.batas"></span> pelanggaran
                    </span>
                </template>

                {{-- Status simpan: titik di HP, titik dan teks di layar lebar --}}
                <span class="flex items-center gap-1.5 text-xs" style="color: var(--tinta-pudar);" aria-live="polite">
                    <span class="inline-block w-2 h-2 rounded-full"
                          :style="'background:' + (status === 'gagal' ? 'var(--bahaya)' : (status === 'menyimpan' ? 'var(--ragu)' : 'var(--sukses)'))"></span>
                    <span class="hidden sm:inline" x-text="status === 'gagal' ? 'Gagal menyimpan' : (status === 'menyimpan' ? 'Menyimpan' : 'Tersimpan')"></span>
                </span>

                <div class="waktu font-bold text-base sm:text-lg px-2.5 py-1 rounded-lg"
                     :style="sisa <= 300 ? 'background: var(--bahaya-muda); color: var(--bahaya)' : 'background: var(--latar); color: var(--tinta)'"
                     x-text="waktu"></div>
            </div>
        </div>
        <div class="progres" role="progressbar" aria-label="Soal terjawab" :aria-valuenow="persen" aria-valuemin="0" aria-valuemax="100">
            <div :style="'width:' + persen + '%'"></div>
        </div>
    </header>

    <div x-show="status === 'gagal'" class="text-white text-sm text-center py-2 px-4" style="background: var(--bahaya);">
        Koneksi bermasalah. Jawaban dikirim ulang otomatis.
    </div>

    <main class="w-full max-w-6xl mx-auto sm:px-5 sm:pt-5 pb-28 lg:pb-8 grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_17rem] gap-4">

        {{-- Soal: selebar layar di HP, kartu di laptop --}}
        <section class="min-w-0 border-b lg:border lg:rounded-xl px-4 py-5 sm:p-7" style="background: var(--kertas); border-color: var(--garis);">

            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-medium" style="color: var(--tinta-pudar);">
                    Soal <span class="waktu" x-text="idx + 1"></span> dari <span class="waktu" x-text="soal.length"></span>
                </p>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-md shrink-0"
                      style="background: var(--ragu-muda); color: var(--ragu-teks);" x-show="ragu[s.id]">Ragu-ragu</span>
            </div>

            <template x-if="s.stimulus">
                <div class="teks-soal mt-4 p-4 rounded-xl whitespace-pre-line break-words overflow-x-auto"
                     style="background: var(--latar);" x-text="s.stimulus"></div>
            </template>

            <template x-if="s.gambar">
                <img :src="s.gambar" alt="Gambar soal" class="mt-4 max-w-full h-auto max-h-96 object-contain rounded-lg border" style="border-color: var(--garis);">
            </template>

            <p class="teks-soal mt-4 whitespace-pre-line break-words" x-text="s.pertanyaan"></p>

            <p class="mt-3 text-sm" style="color: var(--tinta-pudar);" x-show="s.tipe === 'pg'">Pilih satu jawaban.</p>
            <p class="mt-3 text-sm" style="color: var(--tinta-pudar);" x-show="s.tipe === 'pg_kompleks'">Pilih dua jawaban.</p>
            <p class="mt-3 text-sm" style="color: var(--tinta-pudar);" x-show="s.tipe === 'benar_salah'">Tentukan benar atau salah.</p>

            {{-- Pilihan ganda --}}
            <template x-if="s.tipe === 'pg'">
                <div class="mt-3 space-y-2.5">
                    <template x-for="o in s.opsi" :key="s.id + '-' + o.kode">
                        <label class="opsi" :class="jawab[s.id] === o.kode ? 'aktif' : ''">
                            <input type="radio" :name="'pg-' + s.id"
                                   :checked="jawab[s.id] === o.kode" @change="pilihPg(o.kode)">
                            <span class="huruf" x-text="o.kode"></span>
                            <span class="min-w-0 break-words pt-1">
                                <span x-text="o.teks"></span>
                                <template x-if="o.gambar">
                                    <img :src="o.gambar" alt="Gambar opsi" class="mt-2 max-w-full h-auto max-h-40 object-contain rounded-lg border" style="border-color: var(--garis);">
                                </template>
                            </span>
                        </label>
                    </template>
                </div>
            </template>

            {{-- Pilihan ganda kompleks --}}
            <template x-if="s.tipe === 'pg_kompleks'">
                <div class="mt-3 space-y-2.5">
                    <template x-for="o in s.opsi" :key="s.id + '-' + o.kode">
                        <label class="opsi"
                               :class="[
                                   (jawab[s.id] || []).includes(o.kode) ? 'aktif' : '',
                                   (! (jawab[s.id] || []).includes(o.kode) && (jawab[s.id] || []).length >= 2) ? 'mati' : ''
                               ]">
                            <input type="checkbox"
                                   :checked="(jawab[s.id] || []).includes(o.kode)"
                                   :disabled="! (jawab[s.id] || []).includes(o.kode) && (jawab[s.id] || []).length >= 2"
                                   @change="pilihKompleks(o.kode)">
                            <span class="huruf kotak-centang" x-text="o.kode"></span>
                            <span class="min-w-0 break-words pt-1">
                                <span x-text="o.teks"></span>
                                <template x-if="o.gambar">
                                    <img :src="o.gambar" alt="Gambar opsi" class="mt-2 max-w-full h-auto max-h-40 object-contain rounded-lg border" style="border-color: var(--garis);">
                                </template>
                            </span>
                        </label>
                    </template>
                </div>
            </template>

            {{-- Benar / Salah --}}
            <template x-if="s.tipe === 'benar_salah'">
                <div class="mt-3 space-y-2.5">
                    <template x-for="o in s.opsi" :key="s.id + '-' + o.kode">
                        <div class="pernyataan">
                            <div class="flex items-start gap-3">
                                <span class="huruf shrink-0" x-text="o.kode"></span>
                                <span class="min-w-0 break-words pt-1">
                                    <span x-text="o.teks"></span>
                                    <template x-if="o.gambar">
                                        <img :src="o.gambar" alt="Gambar pernyataan" class="mt-2 max-w-full h-auto max-h-40 object-contain rounded-lg border" style="border-color: var(--garis);">
                                    </template>
                                </span>
                            </div>
                            <div class="seg">
                                <button type="button" @click="pilihBs(o.kode, true)"
                                        :class="(jawab[s.id] || {})[o.kode] === true ? 'benar' : ''"
                                        :aria-pressed="(jawab[s.id] || {})[o.kode] === true">Benar</button>
                                <button type="button" @click="pilihBs(o.kode, false)"
                                        :class="(jawab[s.id] || {})[o.kode] === false ? 'salah' : ''"
                                        :aria-pressed="(jawab[s.id] || {})[o.kode] === false">Salah</button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Navigasi: menempel di bawah layar pada HP, mengalir biasa di laptop --}}
            <div class="fixed bottom-0 inset-x-0 z-20 bg-white border-t px-3 pt-2.5 flex items-center gap-2
                        lg:static lg:z-auto lg:bg-transparent lg:mt-8 lg:px-0 lg:pt-5"
                 style="border-color: var(--garis); padding-bottom: calc(.625rem + env(safe-area-inset-bottom, 0px));">

                <button type="button" @click="ke(idx - 1)" :disabled="idx === 0" class="tombol t-garis !px-3.5" aria-label="Soal sebelumnya">
                    <span aria-hidden="true">&larr;</span><span class="hidden lg:inline">Sebelumnya</span>
                </button>

                <button type="button" @click="toggleRagu()" class="tombol t-ragu flex-1 lg:flex-none" :class="ragu[s.id] ? 'aktif' : ''">
                    <span x-text="ragu[s.id] ? 'Batal ragu' : 'Ragu-ragu'"></span>
                </button>

                <button type="button" @click="panel = true" class="tombol t-garis waktu lg:hidden" aria-label="Buka daftar soal">
                    <span x-text="(idx + 1) + '/' + soal.length"></span>
                </button>

                <div class="hidden lg:block flex-1"></div>

                <template x-if="idx < soal.length - 1">
                    <button type="button" @click="ke(idx + 1)" class="tombol t-utama !px-3.5" aria-label="Soal berikutnya">
                        <span class="hidden lg:inline">Berikutnya</span><span aria-hidden="true">&rarr;</span>
                    </button>
                </template>
                <template x-if="idx === soal.length - 1">
                    <button type="button" @click="kirim()" class="tombol t-gelap">Kirim</button>
                </template>
            </div>
        </section>

        {{-- Daftar nomor: laci di HP, panel samping di laptop --}}
        <div x-show="panel" x-cloak @click="panel = false" class="fixed inset-0 z-30 lg:hidden" style="background: rgba(23, 32, 51, .5);"></div>

        <aside class="min-w-0 border p-4 fixed inset-x-0 bottom-0 z-40 max-h-[80vh] overflow-y-auto rounded-t-2xl
                      lg:static lg:z-auto lg:max-h-none lg:rounded-xl lg:sticky lg:top-20 lg:h-fit"
               :class="panel ? '' : 'hidden lg:block'"
               style="background: var(--kertas); border-color: var(--garis); padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0px));">

            <div class="flex items-center justify-between">
                <h3 class="font-semibold">Nomor soal</h3>
                <button type="button" @click="panel = false" class="h-10 px-2 -mr-2 text-sm font-medium lg:hidden" style="color: var(--utama);">Tutup</button>
            </div>

            <div class="mt-2 grid grid-cols-6 sm:grid-cols-8 lg:grid-cols-5 gap-2">
                <template x-for="(q, i) in soal" :key="q.id">
                    <button type="button" @click="ke(i); panel = false"
                            class="nomor" :class="kelasNav(i)" x-text="i + 1"
                            :aria-label="'Soal nomor ' + (i + 1)"></button>
                </template>
            </div>

            <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1.5 text-xs" style="color: var(--tinta-pudar);">
                <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm" style="background: var(--utama);"></span>Dijawab</span>
                <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm border" style="background: var(--ragu-muda); border-color: var(--ragu);"></span>Ragu</span>
                <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm border" style="background: #fff; border-color: var(--garis-tegas);"></span>Kosong</span>
            </div>

            <p class="mt-4 text-sm">
                <strong class="waktu" x-text="jumlahTerjawab"></strong> dari <span class="waktu" x-text="soal.length"></span> terjawab
            </p>

            <button type="button" @click="kirim()" class="tombol t-gelap w-full mt-3">Kirim jawaban</button>
        </aside>
    </main>

    {{-- Layar peringatan anti-curang --}}
    <div x-show="layar !== ''" x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-6 overflow-y-auto"
         style="background: rgba(23, 32, 51, 0.96);">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl max-w-md w-full p-6 pb-8 sm:p-7"
             style="padding-bottom: calc(2rem + env(safe-area-inset-bottom, 0px));">

            {{-- Masuk layar penuh --}}
            <template x-if="layar === 'mulai'">
                <div>
                    <h3 class="text-xl font-bold">Ujian layar penuh</h3>
                    <p class="mt-2 text-sm" style="color: var(--tinta-pudar);">
                        Pindah tab atau aplikasi, atau keluar dari layar penuh, dicatat sebagai pelanggaran
                        dan bisa dilihat pengawas.
                    </p>

                    <template x-if="pel.batas > 1">
                        <p class="mt-4 text-sm font-medium p-3 rounded-lg" style="background: var(--bahaya-muda); color: var(--bahaya);">
                            Batas <span x-text="pel.batas"></span> pelanggaran. Setelah itu ujian berakhir
                            dan jawaban dikirim otomatis.
                            <template x-if="pel.jumlah > 0">
                                <span>Anda sudah melanggar <span x-text="pel.jumlah"></span> kali.</span>
                            </template>
                        </p>
                    </template>
                    <template x-if="pel.batas === 1">
                        <p class="mt-4 text-sm font-medium p-3 rounded-lg" style="background: var(--bahaya-muda); color: var(--bahaya);">
                            Satu pelanggaran langsung mengakhiri ujian dan mengirim jawaban.
                        </p>
                    </template>

                    <button type="button" @click="mulaiLayarPenuh()" class="tombol t-utama w-full mt-5 !h-12">
                        Masuk layar penuh
                    </button>
                </div>
            </template>

            {{-- Peringatan pelanggaran --}}
            <template x-if="layar === 'peringatan'">
                <div>
                    <h3 class="text-xl font-bold" style="color: var(--bahaya);">Peringatan</h3>
                    <p class="mt-2 text-sm" x-text="pesanPel"></p>

                    <template x-if="pel.batas > 0">
                        <div class="mt-4 p-4 rounded-xl text-center" style="background: var(--bahaya-muda);">
                            <p class="text-4xl font-bold waktu" style="color: var(--bahaya);">
                                <span x-text="pel.jumlah"></span>/<span x-text="pel.batas"></span>
                            </p>
                            <p class="mt-1 text-sm">
                                Sisa <strong x-text="Math.max(0, pel.batas - pel.jumlah)"></strong> kali sebelum
                                ujian berakhir dan jawaban dikirim.
                            </p>
                        </div>
                    </template>
                    <template x-if="pel.batas === 0">
                        <p class="mt-3 text-sm" style="color: var(--tinta-pudar);">Dicatat dan bisa dilihat pengawas.</p>
                    </template>

                    <button type="button" @click="lanjutkan()" class="tombol t-utama w-full mt-5 !h-12">
                        Kembali ke ujian
                    </button>
                </div>
            </template>
        </div>
    </div>

    <form id="form-kirim" method="POST" action="{{ route('siswa.ujian.kirim', $sesi) }}" class="hidden">
        @csrf
    </form>
</body>
</html>