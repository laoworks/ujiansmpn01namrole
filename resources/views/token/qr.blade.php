<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR Ujian: {{ $ujian->mapel->nama }}</title>
    <style>
        :root {
            --latar: #12203a;
            --kertas: #ffffff;
            --tinta: #162033;
            --pudar: #9fb0cc;
            --utama: #2f6bff;
            --sukses: #34c38f;
            --bahaya: #ff6b5e;
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        body {
            background: var(--latar);
            color: #fff;
            font-family: "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
            display: flex;
            flex-direction: column;
        }

        .atas {
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
            padding: 14px 28px;
        }
        .atas a, .atas button, .aksi button {
            color: var(--pudar); font-size: 14px; background: none; border: 1px solid #2c3d5e;
            padding: 8px 14px; border-radius: 8px; cursor: pointer; text-decoration: none; font-family: inherit;
        }
        .atas a:hover, .atas button:hover, .aksi button:hover:not(:disabled) { color: #fff; border-color: #4a6190; }
        .aksi button:disabled { opacity: .4; cursor: not-allowed; }
        .aksi .utama { background: var(--utama); border-color: var(--utama); color: #fff; }
        .aksi .utama:hover:not(:disabled) { background: #1f56e0; border-color: #1f56e0; }

        main {
            flex: 1;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: clamp(24px, 5vw, 72px);
            align-items: center;
            padding: 0 clamp(20px, 5vw, 72px) 32px;
            max-width: 1280px; width: 100%; margin: 0 auto;
        }

        .info h1 { font-size: clamp(28px, 4.2vw, 56px); line-height: 1.1; margin: 0; font-weight: 700; }
        .info .judul { margin-top: 10px; font-size: clamp(16px, 1.8vw, 22px); color: var(--pudar); }
        .info .kelas { margin-top: 4px; font-size: clamp(14px, 1.5vw, 18px); color: var(--pudar); }

        .langkah { margin: 32px 0 0; padding: 0; list-style: none; display: grid; gap: 10px; font-size: clamp(15px, 1.6vw, 20px); }
        .langkah li { display: flex; gap: 12px; align-items: baseline; }
        .langkah b { color: var(--utama); min-width: 1.2em; }

        .hitung { margin-top: 32px; }
        .hitung .angka { font-size: clamp(32px, 4vw, 52px); font-weight: 700; font-variant-numeric: tabular-nums; }
        .hitung .angka small { font-size: .45em; font-weight: 400; color: var(--pudar); margin-left: 8px; }
        .batang { margin-top: 8px; height: 8px; border-radius: 99px; background: #243556; overflow: hidden; max-width: 420px; }
        .batang div { height: 100%; width: 0; background: var(--sukses); transition: width .4s ease; }

        .qr { display: flex; flex-direction: column; align-items: center; }
        .kartu {
            background: var(--kertas); border-radius: 20px; padding: clamp(14px, 2vw, 24px);
            width: min(100%, 520px); aspect-ratio: 1 / 1; display: grid; place-items: center;
        }
        .kartu svg { width: 100%; height: 100%; display: block; }
        .kartu .tunggu { color: var(--tinta); font-size: 16px; }

        .kode { margin-top: 22px; text-align: center; }
        .kode .teks {
            font-family: "Consolas", "SF Mono", Menlo, monospace;
            font-size: clamp(30px, 4.4vw, 58px); font-weight: 700; letter-spacing: .14em;
            font-variant-numeric: tabular-nums;
        }
        .kode .ket { margin-top: 4px; font-size: 14px; color: var(--pudar); }

        .aksi { margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }

        .galat { display: none; margin-top: 14px; padding: 10px 14px; border-radius: 10px; background: #3a1f24; color: var(--bahaya); font-size: 14px; }

        @media (max-width: 820px) {
            main { grid-template-columns: 1fr; align-content: start; }
            .qr { order: -1; }
        }
        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }

        /* Tampilan cetak: latar putih, hanya judul, QR, dan kode */
        @page { margin: 15mm; }
        @media print {
            html, body { height: auto; }
            body { background: #fff; color: #000; display: block; }
            .atas, .hitung, .aksi, .galat { display: none !important; }
            main { display: block; max-width: none; padding: 0; }
            .info { text-align: center; }
            .info h1 { font-size: 36px; }
            .info .judul, .info .kelas { color: #000; }
            .langkah { justify-items: center; color: #000; }
            .langkah b { color: #000; }
            .qr { order: 0; margin-top: 24px; }
            .kartu { width: 14cm; border: 2px solid #000; border-radius: 0; }
            .kode .teks { color: #000; font-size: 40px; }
            .kode .ket { color: #333; }
        }
    </style>
</head>
<body>
    <div class="atas">
        <a href="{{ route('token.index', $ujian) }}">Kembali ke daftar token</a>
        <button type="button" id="btn-layar">Layar penuh</button>
    </div>

    <main>
        <section class="info">
            <h1>{{ $ujian->mapel->nama }}</h1>
            <div class="judul">{{ $ujian->judul }}</div>
            @if ($namaKelas !== '')
                <div class="kelas">Kelas {{ $namaKelas }}</div>
            @endif

            <ol class="langkah">
                <li><b>1</b><span>Buka aplikasi ujian di HP.</span></li>
                <li><b>2</b><span>Ketuk Pindai QR, lalu arahkan kamera ke kode di samping.</span></li>
                <li><b>3</b><span>Pengguna laptop: ketik kode di bawah QR pada kolom kode ujian.</span></li>
            </ol>

            <div class="hitung">
                <div class="angka"><span id="masuk">0</span><small>dari <span id="total">0</span> siswa sudah masuk</small></div>
                <div class="batang"><div id="batang-masuk"></div></div>
            </div>
        </section>

        <section class="qr">
            <div class="kartu" id="qr"><span class="tunggu">Memuat QR...</span></div>

            <div class="kode">
                <div class="teks" id="kode">-</div>
                <div class="ket">Kode ujian</div>
            </div>

            <div class="aksi">
                <button type="button" id="btn-png" class="utama" disabled>Unduh PNG</button>
                <button type="button" id="btn-svg" disabled>Unduh SVG</button>
                <button type="button" id="btn-cetak" disabled>Cetak</button>
            </div>

            <div class="galat" id="galat"></div>
        </section>
    </main>

    <script>
        const URL_DATA = @js(route('token.qr.data', $ujian));
        const NAMA_FILE = @js('qr-' . \Illuminate\Support\Str::slug($ujian->mapel->nama . ' ' . $ujian->judul));
        const JUDUL_CETAK = @js($ujian->mapel->nama . ' - ' . $ujian->judul);
        const el = (id) => document.getElementById(id);

        let memuat = false;
        let qrSiap = false;

        function tampilGalat(teks) {
            const g = el('galat');
            g.textContent = teks;
            g.style.display = teks ? 'block' : 'none';
        }

        async function muat() {
            if (memuat) return;
            memuat = true;
            try {
                const r = await fetch(URL_DATA, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', cache: 'no-store' });
                if (r.status === 409) { tampilGalat('Ujian tidak lagi aktif.'); return; }
                if (!r.ok) throw new Error('gagal');

                const d = await r.json();

                // QR tetap: cukup dipasang sekali
                if (!qrSiap) {
                    el('qr').innerHTML = d.svg;
                    el('kode').textContent = d.kode_tampil;
                    qrSiap = true;
                    ['btn-png', 'btn-svg', 'btn-cetak'].forEach((id) => el(id).disabled = false);
                }

                el('masuk').textContent = d.masuk;
                el('total').textContent = d.total;
                el('batang-masuk').style.width = (d.total ? Math.min(100, Math.round(d.masuk / d.total * 100)) : 0) + '%';
                tampilGalat('');
            } catch (e) {
                tampilGalat('Tidak bisa memuat data. Memeriksa lagi...');
            } finally {
                memuat = false;
            }
        }

        // Hanya memperbarui jumlah siswa yang sudah masuk
        setInterval(muat, 10000);

        // ---------- Unduh & cetak ----------
        function simpanBlob(blob, nama) {
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = nama;
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(() => URL.revokeObjectURL(a.href), 1000);
        }

        function svgSebagaiTeks(ukuran) {
            const asli = el('qr').querySelector('svg');
            if (!asli) return null;
            const c = asli.cloneNode(true);
            c.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            c.setAttribute('width', ukuran);
            c.setAttribute('height', ukuran);
            return new XMLSerializer().serializeToString(c);
        }

        el('btn-svg').addEventListener('click', () => {
            const xml = svgSebagaiTeks(1000);
            if (xml) simpanBlob(new Blob([xml], { type: 'image/svg+xml' }), NAMA_FILE + '.svg');
        });

        el('btn-png').addEventListener('click', () => {
            const ukuran = 1000, pad = 80, atas = 120, bawah = 140;
            const xml = svgSebagaiTeks(ukuran);
            if (!xml) return;

            const img = new Image();
            img.onload = () => {
                const cv = document.createElement('canvas');
                cv.width = ukuran + pad * 2;
                cv.height = atas + ukuran + bawah + pad;
                const g = cv.getContext('2d');

                g.fillStyle = '#ffffff';
                g.fillRect(0, 0, cv.width, cv.height);

                g.fillStyle = '#000000';
                g.textAlign = 'center';
                g.font = 'bold 44px "Segoe UI", Arial, sans-serif';
                g.fillText(JUDUL_CETAK, cv.width / 2, 70, cv.width - pad);

                g.drawImage(img, pad, atas, ukuran, ukuran);

                g.font = 'bold 72px Consolas, Menlo, monospace';
                g.fillText(el('kode').textContent, cv.width / 2, atas + ukuran + 90, cv.width - pad);

                cv.toBlob((b) => simpanBlob(b, NAMA_FILE + '.png'), 'image/png');
            };
            img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(xml);
        });

        el('btn-cetak').addEventListener('click', () => window.print());

        el('btn-layar').addEventListener('click', () => {
            if (document.fullscreenElement) document.exitFullscreen();
            else document.documentElement.requestFullscreen?.();
        });

        muat();
    </script>
</body>
</html>