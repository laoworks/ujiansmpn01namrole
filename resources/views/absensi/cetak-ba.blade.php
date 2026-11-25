@extends('absensi.layout-cetak')

@section('judul', 'Berita Acara - '.$kelas->nama)

@push('gaya')
<style>
    h2.judul { text-align: center; font-size: 13pt; margin: 4px 0 18px; line-height: 1.5; }
    table.isi { width: 100%; border-collapse: collapse; }
    table.isi td { padding: 3px 0; vertical-align: top; }
    td.lbl { width: 42%; padding-left: 28px !important; }
    td.sep { width: 3%; }
    .titik { border-bottom: 1px dotted #000; height: 26px; }
    table.ttd { width: 100%; border-collapse: collapse; margin-top: 8px; }
    table.ttd td { width: 50%; padding: 5px 8px; vertical-align: middle; }
    table.ttd td.judulttd { font-weight: bold; text-align: center; padding-bottom: 12px; }
    img.ttd-img { height: 52px; vertical-align: middle; }
</style>
@endpush

@section('isi')
    @php($titik = str_repeat('.', 28))

    <h2 class="judul">
        BERITA ACARA PELAKSANAAN UJIAN SEKOLAH<br>
        TAHUN PELAJARAN {{ $sekolah['tahun_pelajaran'] }}
    </h2>

    <p>
        Pada hari ini <strong>{{ $tgl['hari'] }}</strong>,
        Tanggal <strong>{{ $tgl['tanggal'] }}</strong>,
        Bulan <strong>{{ $tgl['bulan'] }}</strong>,
        Tahun <strong>{{ $tgl['tahun'] }}</strong>
    </p>

    <table class="isi">
        <tr>
            <td colspan="3">
                a. Telah diselenggarakan Ujian Satuan Pendidikan dari Pukul
                <strong>{{ $ba->pukul_mulai }}</strong> s/d pukul <strong>{{ $ba->pukul_selesai }}</strong>
            </td>
        </tr>
        <tr><td class="lbl">Pada sekolah</td><td class="sep">:</td><td>{{ $sekolah['nama'] }}</td></tr>
        <tr><td class="lbl">Ruang</td><td class="sep">:</td><td>{{ $ba->ruang }}</td></tr>
        <tr><td class="lbl">Jumlah Peserta Seharusnya</td><td class="sep">:</td><td>{{ $seharusnya }} Orang</td></tr>
        <tr><td class="lbl">Jumlah Peserta Yang Tidak Hadir</td><td class="sep">:</td><td>{{ $tidakHadir }} Orang</td></tr>
        <tr><td class="lbl">Jumlah Peserta Yang Hadir</td><td class="sep">:</td><td>{{ $hadir }} Orang</td></tr>
    </table>

    <p style="margin-top: 14px;">
        b. Telah dilaksanakan Ujian Sekolah (US) untuk mata pelajaran
        <strong>{{ $ujian->mapel->nama }}</strong> secara online dengan menggunakan Android
    </p>

    <p style="margin-bottom: 4px;">c. Catatan selama Ujian Sekolah (US) berlangsung :</p>
    @if (filled($ba->catatan))
        <div style="white-space: pre-line; margin: 0 0 8px 20px;">{{ $ba->catatan }}</div>
    @else
        @for ($i = 0; $i < 5; $i++)
            <div class="titik"></div>
        @endfor
    @endif

    <p style="margin-top: 14px;">Berita acara ini dibuat dengan sesungguhnya.</p>

    <p style="text-align: center; margin: 22px 0 6px;">Yang membuat berita acara:</p>

    <table class="ttd">
        <tr>
            <td class="judulttd">Pengawas I</td>
            <td class="judulttd">Pengawas II</td>
        </tr>
        <tr>
            <td>1. Tanda Tangan :
                @if ($ttd1)<img class="ttd-img" src="{{ $ttd1 }}" alt="">@else{{ $titik }}@endif
            </td>
            <td>1. Tanda Tangan :
                @if ($ttd2)<img class="ttd-img" src="{{ $ttd2 }}" alt="">@else{{ $titik }}@endif
            </td>
        </tr>
        <tr>
            <td>2. Nama : <strong>{{ $ba->pengawas1_nama ?: $titik }}</strong></td>
            <td>2. Nama : <strong>{{ $ba->pengawas2_nama ?: $titik }}</strong></td>
        </tr>
        <tr>
            <td>3. NIP : {{ $ba->pengawas1_nip ?: $titik }}</td>
            <td>3. NIP : {{ $ba->pengawas2_nip ?: $titik }}</td>
        </tr>
    </table>
@endsection