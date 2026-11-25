@php
    $kop = config('ujian.sekolah');
    $logoKiri = file_exists(public_path('images/logo-kiri.png')) ? asset('images/logo-kiri.png') : null;
    $logoKanan = file_exists(public_path('images/logo-kanan.png')) ? asset('images/logo-kanan.png') : null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul')</title>
    <style>
        @page { size: A4; margin: 15mm 18mm; }
        body { font-family: "Times New Roman", serif; font-size: 12pt; color: #000; margin: 0; padding: 16px; background: #fff; }
        .halaman { max-width: 800px; margin: 0 auto; }
        .tombol { margin-bottom: 14px; font-family: Arial, sans-serif; }
        .tombol button { padding: 8px 16px; cursor: pointer; }
        .kop { width: 100%; border-collapse: collapse; text-align: center; }
        .kop td.logo { width: 80px; }
        .kop img { max-width: 70px; max-height: 70px; }
        .kop .besar { font-size: 15pt; font-weight: bold; }
        .kop .kecil { font-size: 9.5pt; }
        .garis { border: 0; border-top: 3px double #000; margin: 6px 0 14px; }
        @media print { .tombol { display: none; } body { padding: 0; } }
    </style>
    @stack('gaya')
</head>
<body>
<div class="halaman">
    <div class="tombol">
        <button onclick="window.print()">Cetak</button>
        <button onclick="window.close()">Tutup</button>
    </div>

    <table class="kop">
        <tr>
            <td class="logo">@if ($logoKiri)<img src="{{ $logoKiri }}" alt="">@endif</td>
            <td>
                <div>{{ $kop['pemerintah'] }}</div>
                <div>{{ $kop['dinas'] }}</div>
                <div class="besar">{{ $kop['nama'] }}</div>
                <div class="kecil">{{ $kop['alamat'] }}</div>
            </td>
            <td class="logo">@if ($logoKanan)<img src="{{ $logoKanan }}" alt="">@endif</td>
        </tr>
    </table>
    <hr class="garis">

    @yield('isi')
</div>
</body>
</html>