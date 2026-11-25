<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogPelanggaran extends Model
{
    protected $table = 'log_pelanggaran';

    protected $fillable = ['sesi_ujian_id', 'jenis', 'keterangan', 'dihitung'];

    /** Jenis yang menambah hitungan pelanggaran */
    public const DIHITUNG = [
        'keluar_halaman',
        'keluar_layar_penuh',
        'keluar_aplikasi',
    ];

    public const LABEL = [
        'keluar_halaman' => 'Meninggalkan halaman ujian',
        'keluar_layar_penuh' => 'Keluar dari layar penuh',
        'salin_tempel' => 'Mencoba salin / tempel',
        'klik_kanan' => 'Menu klik kanan / tekan lama',
        'pintasan' => 'Pintasan keyboard terlarang',
        'screenshot' => 'Menekan PrintScreen',
        'keluar_aplikasi' => 'Keluar dari aplikasi ujian',
    ];

    protected function casts(): array
    {
        return ['dihitung' => 'boolean'];
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiUjian::class, 'sesi_ujian_id');
    }
}