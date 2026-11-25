<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeritaAcara extends Model
{
    protected $table = 'berita_acara';

    protected $fillable = [
        'ujian_id', 'kelas_id', 'ruang', 'tanggal', 'pukul_mulai', 'pukul_selesai',
        'pengawas1_nama', 'pengawas1_nip', 'pengawas1_ttd',
        'pengawas2_nama', 'pengawas2_nip', 'pengawas2_ttd',
        'catatan',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }
}