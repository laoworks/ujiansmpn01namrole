<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kartu Token - {{ $ujian->judul }}</title>
    <style>
        @page { size: A4; margin: 8mm; }

        * { box-sizing: border-box; }

        :root {
            --warna: #0f766e;
            --garis: #6b7280;
            --kolom: 3;
            --tinggi: 33mm;
            --token-size: 18px;
            --token-spasi: 3px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 20px;
            color: #111827;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Mode 4 kolom (lebih padat) */
        body.padat {
            --kolom: 4;
            --tinggi: 30mm;
            --token-size: 14px;
            --token-spasi: 2px;
        }

        /* ---------- Bilah kontrol (tidak ikut tercetak) ---------- */
        .tombol {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 18px;
            padding: 12px 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .tombol button {
            padding: 8px 18px;
            cursor: pointer;
            background: #0d9488;
            color: #fff;
            border: 0;
            border-radius: 6px;
            font-size: 14px;
        }
        .tombol button:hover { background: #0f766e; }
        .tombol label { font-size: 13px; color: #374151; cursor: pointer; display: flex; align-items: center; gap: 6px; }
        .petunjuk { font-size: 12px; color: #6b7280; flex: 1 1 260px; }

        /* ---------- Judul per kelas ---------- */
        .kelas-bagian { margin-bottom: 4mm; }
        .kelas-judul {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 10px;
            padding-bottom: 1mm;
            margin-bottom: 1.5mm;
            border-bottom: 1.5px solid var(--warna);
            font-size: 10px;
            break-after: avoid;
            page-break-after: avoid;
        }
        .kelas-judul strong { font-size: 11px; }
        .kelas-judul .meta { color: #4b5563; white-space: nowrap; }

        body.halaman-baru .kelas-bagian + .kelas-bagian {
            break-before: page;
            page-break-before: always;
        }

        /* ---------- Grid kartu ---------- */
        .grid {
            display: grid;
            grid-template-columns: repeat(var(--kolom), 1fr);
            border-top: 1px dashed var(--garis);
            border-left: 1px dashed var(--garis);
        }

        .kartu {
            height: var(--tinggi);
            padding: 1.5mm;
            border-right: 1px dashed var(--garis);
            border-bottom: 1px dashed var(--garis);
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .isi {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.2mm 2mm;
            border: 1px solid #9ca3af;
            border-radius: 1.5mm;
            overflow: hidden;
        }

        .atas {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 4px;
            padding-bottom: 0.8mm;
            border-bottom: 1px solid var(--warna);
            font-size: 8px;
        }
        .atas .judul {
            font-weight: bold;
            color: var(--warna);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }
        .atas .kelas {
            flex-shrink: 0;
            font-weight: bold;
            padding: 0 1.5mm;
            border: 1px solid var(--warna);
            border-radius: 999px;
            color: var(--warna);
        }

        .siswa {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 3mm;
        }
        .nama {
            font-size: 10px;
            font-weight: bold;
            line-height: 1.2;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-width: 0;
        }
        .nis {
            flex-shrink: 0;
            font-size: 9px;
            font-family: Consolas, "Courier New", monospace;
            color: #374151;
        }

        .token {
            text-align: center;
            font-family: Consolas, "Courier New", monospace;
            font-size: var(--token-size);
            font-weight: bold;
            letter-spacing: var(--token-spasi);
            padding: 0.5mm 0 0.5mm var(--token-spasi);
            border: 1.5px solid var(--warna);
            border-radius: 1mm;
            line-height: 1.2;
        }

        .bawah {
            display: flex;
            justify-content: space-between;
            gap: 4px;
            font-size: 6.5px;
            color: #4b5563;
            line-height: 1.2;
        }
        .bawah .rahasia { color: #b91c1c; white-space: nowrap; }

        @media print {
            .tombol { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body class="halaman-baru-off">
    <div class="tombol">
        <button onclick="window.print()">Cetak</button>
        <label>
            <input type="checkbox" onchange="document.body.classList.toggle('padat', this.checked)">
            Lebih padat (4 kolom)
        </label>
        <label>
            <input type="checkbox" onchange="document.body.classList.toggle('halaman-baru', this.checked)">
            Tiap kelas di halaman baru
        </label>
        <span class="petunjuk">
            Cetak di kertas A4 dengan skala 100%, lalu gunting mengikuti garis putus-putus.
        </span>
    </div>

    @foreach ($perKelas as $namaKelas => $daftar)
        <section class="kelas-bagian">
            <div class="kelas-judul">
                <strong>Kelas {{ $namaKelas }} &middot; {{ $daftar->count() }} siswa</strong>
                <span class="meta">
                    {{ $ujian->judul }} &middot; {{ $ujian->mulai_at->format('d M Y H:i') }} s/d {{ $ujian->selesai_at->format('d M Y H:i') }}
                </span>
            </div>

            <div class="grid">
                @foreach ($daftar as $s)
                    <div class="kartu">
                        <div class="isi">
                            <div class="atas">
                                <span class="judul">{{ $ujian->judul }} &middot; {{ $ujian->mapel->nama }}</span>
                                <span class="kelas">{{ $namaKelas }}</span>
                            </div>

                            <div class="siswa">
                                <div class="nama">{{ $s->name }}</div>
                                <div class="nis">{{ $s->username }}</div>
                            </div>

                            <div class="token">{{ $tokens[$s->id]->token ?? '-' }}</div>

                            <div class="bawah">
                                <span>{{ $ujian->mulai_at->format('d/m/y H:i') }} &middot; {{ $ujian->durasi_menit }} mnt</span>
                                <span class="rahasia">Rahasia, sekali pakai</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach

    @if ($perKelas->isEmpty())
        <p>Belum ada token yang dibuat.</p>
    @endif
</body>
</html>