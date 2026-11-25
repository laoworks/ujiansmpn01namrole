@php
    $ya = fn ($b) => $b ? 'Ya' : 'Tidak';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cek perangkat</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 16px; color: #111827; }
        .kartu { background: #fff; max-width: 720px; margin: 0 auto; padding: 24px; border-radius: 12px;
                 box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        h1 { font-size: 20px; margin: 0 0 12px; }
        .hasil { padding: 12px; border-radius: 8px; font-size: 16px; margin-bottom: 16px; }
        .ok { background: #dcfce7; color: #166534; }
        .no { background: #fee2e2; color: #991b1b; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        td { border-top: 1px solid #e5e7eb; padding: 8px 6px; vertical-align: top; }
        td:first-child { width: 38%; color: #4b5563; }
        code { word-break: break-all; font-size: 12px; }
        .kecil { font-size: 12px; color: #6b7280; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="kartu">
        <h1>Cek perangkat</h1>

        @if (! $aktif)
            <div class="hasil" style="background:#fef3c7;color:#92400e;">
                Pembatasan perangkat sedang nonaktif (UJIAN_PENJAGA=false). Semua perangkat boleh masuk.
            </div>
        @endif

        @if ($p['sah'])
            <div class="hasil ok">
                <strong>DIIZINKAN</strong> sebagai {{ $p['jenis'] }}
                @if ($p['kode'] === 'browser')
                    <div style="font-size:13px;margin-top:4px;">
                        Batas pelanggaran: {{ (int) config('ujian.batas_browser', 2) }} kali.
                    </div>
                @else
                    <div style="font-size:13px;margin-top:4px;">
                        Batas pelanggaran: {{ (int) config('ujian.batas_app', 1) }} kali.
                    </div>
                @endif
            </div>
        @else
            <div class="hasil no">
                <strong>DITOLAK</strong>:
                perangkat ini bukan aplikasi ujian, Safe Exam Browser yang terdaftar
                @if ($p['browser']['diizinkan'])
                    , maupun Google Chrome
                @endif
            </div>
        @endif

        <table>
            <tr><td>Kunci aplikasi Android</td>
                <td>
                    Tanda aplikasi ditemukan: {{ $ya($p['app']['ada']) }}<br>
                    Kunci terdaftar di server: {{ $ya($p['app']['dikonfigurasi']) }}<br>
                    Kunci cocok: {{ $ya($p['app']['sah']) }}
                </td></tr>
            <tr><td>SEB: Config Key</td>
                <td>
                    Header diterima: {{ $ya($p['seb_config']['ada']) }}<br>
                    Kunci terdaftar di server: {{ $ya($p['seb_config']['dikonfigurasi']) }}<br>
                    Hash cocok: {{ $ya($p['seb_config']['sah']) }}
                </td></tr>
            <tr><td>SEB: Browser Exam Key</td>
                <td>
                    Header diterima: {{ $ya($p['seb_bek']['ada']) }}<br>
                    Kunci terdaftar di server: {{ $ya($p['seb_bek']['dikonfigurasi']) }}<br>
                    Hash cocok: {{ $ya($p['seb_bek']['sah']) }}
                </td></tr>
            <tr><td>Google Chrome</td>
                <td>
                    Chrome diizinkan di server (UJIAN_IZINKAN_BROWSER): {{ $ya($p['browser']['diizinkan']) }}<br>
                    Terdeteksi sebagai Chrome asli: {{ $ya($p['browser']['mirip']) }}<br>
                    Lolos: {{ $ya($p['browser']['sah']) }}
                </td></tr>
            <tr><td>URL yang dihitung server</td><td><code>{{ $p['url'] }}</code></td></tr>
            <tr><td>User-Agent</td><td><code>{{ $p['ua'] }}</code></td></tr>
        </table>

        <p class="kecil">
            Kunci rahasia tidak ditampilkan di halaman ini. Kalau header SEB diterima tetapi hash tidak cocok,
            biasanya alamat di file .seb berbeda dengan alamat yang dipakai server, atau Config Key di .env belum diperbarui.
        </p>
    </div>
</body>
</html>