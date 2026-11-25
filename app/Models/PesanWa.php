<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesanWa extends Model
{
    protected $table = 'pesan_wa';

    protected $fillable = [
        'ujian_id', 'sesi_ujian_id', 'user_id', 'nomor', 'isi', 'status',
        'percobaan', 'coba_lagi_at', 'dikirim_at', 'kesalahan',
    ];

    protected function casts(): array
    {
        return ['coba_lagi_at' => 'datetime', 'dikirim_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiUjian::class, 'sesi_ujian_id');
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class);
    }
}