@extends('absensi.layout-cetak')

@section('judul', 'Daftar Hadir - '.$kelas->nama)

@push('gaya')
<style>
    h2.judul { text-align: center; font-size: 13pt; margin: 4px 0 14px; line-height: 1.5; }
    table.info { border-collapse: collapse; margin-bottom: 10px; }
    table.info td { padding: 1px 6px 1px 0; vertical-align: top; }
    table.daftar { width: 100%; border-collapse: collapse; font-size: 11pt; }
    table.daftar th, table.daftar td { border: 1px solid #000; padding: 4px 6px; }
    table.daftar th { background: #eee; text-align: center; }
    td.no { text-align: center; width: 32px; }
    td.nis { width: 110px; text-align: center; }
    td.ttd { width: 190px; }
    td.ket { width: 90px; font-size: 9pt; }
    img.ttd-img { height: 30px; vertical-align: middle; }
    tr { page-break-inside: avoid; }
    .pengawas { width: 260px; margin: 28px 0 0 auto; text-align: left; }
</style>
@endpush

@section('isi')
    <h2 class="judul">
        DAFTAR HADIR PESERTA<br>
        {{ mb_strtoupper($ujian->judul) }}<br>
        TAHUN PELAJARAN {{ $sekolah['tahun_pelajaran'] }}
    </h2>

    <table class="info">
        <tr><td>Provinsi</td><td>: {{ $sekolah['provinsi'] }}</td></tr>
        <tr><td>Kabupaten / Kota</td><td>: {{ $sekolah['kabupaten'] }}</td></tr>
        <tr><td>Sekolah / Madrasah</td><td>: {{ ucwords(strtolower($sekolah['nama'])) }}</td></tr>
        <tr><td>Mata Pelajaran</td><td>: {{ $ujian->mapel->nama }}</td></tr>
        <tr><td>Hari / Tanggal</td><td>: {{ $tgl['lengkap'] }}</td></tr>
        <tr><td>Ruang Ujian</td><td>: {{ $ba?->ruang ?? $kelas->nama }}</td></tr>
    </table>

    <table class="daftar">
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Peserta (NIS)</th>
                <th>Nama Peserta<br><span style="font-weight: normal; font-size: 9pt;">(isi dengan huruf cetak)</span></th>
                <th>Tanda Tangan Peserta</th>
                <th>Ket.</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($siswa as $s)
                @php($k = $kehadiran->get($s->id))
                <tr>
                    <td class="no">{{ $loop->iteration }}</td>
                    <td class="nis">{{ $s->username }}</td>
                    <td>{{ $s->name }}</td>
                    <td class="ttd" style="text-align: {{ $loop->iteration % 2 ? 'left' : 'right' }};">
                        {{ $loop->iteration }}.
                        @if (! empty($ttdSiswa[$s->id]))
                            <img class="ttd-img" src="{{ $ttdSiswa[$s->id] }}" alt="">
                        @else
                            ..............
                        @endif
                    </td>
                    <td class="ket">
                        @if ($k && ! $k->hadir)
                            Tidak hadir{{ $k->keterangan ? ' ('.$k->keterangan.')' : '' }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pengawas">
        Pengawas I<br>
        @if ($ttdPengawas1)
            <img src="{{ $ttdPengawas1 }}" alt="" style="height: 60px;"><br>
        @else
            <br><br><br>
        @endif
        ( <strong>{{ $ba?->pengawas1_nama ?: str_repeat('.', 28) }}</strong> )<br>
        NIP. {{ $ba?->pengawas1_nip ?: str_repeat('.', 24) }}
    </div>
@endsection