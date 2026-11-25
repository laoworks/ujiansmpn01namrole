<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalUjian extends Model
{
    protected $table = 'ujian_jadwal';

    protected $fillable = ['ujian_id', 'urutan', 'mulai_at', 'selesai_at'];

    protected function casts(): array
    {
        return [
            'mulai_at' => 'datetime',
            'selesai_at' => 'datetime',
        ];
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class);
    }

    public function sesiSiswa(): HasMany
    {
        return $this->hasMany(SesiUjian::class, 'jadwal_id');
    }
}