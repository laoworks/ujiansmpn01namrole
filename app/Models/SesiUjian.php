<?php

namespace App\Models;

use App\Services\Penilai;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SesiUjian extends Model
{
    protected $table = 'sesi_ujian';

    protected $fillable = [
        'ujian_id',
        'user_id',
        'jadwal_id',    // sesi jadwal tempat siswa masuk
        'mulai_at',
        'batas_selesai_at',
        'selesai_at',
        'urutan',
        'kunci',
        'klien',        // app | seb | browser | lain
        'status',
        'skor',
        'skor_maks',
        'nilai',
    ];

    protected function casts(): array
    {
        return [
            'mulai_at' => 'datetime',
            'batas_selesai_at' => 'datetime',
            'selesai_at' => 'datetime',
            'urutan' => 'array',
            'nilai' => 'float',
        ];
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class);
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalUjian::class, 'jadwal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(Jawaban::class, 'sesi_ujian_id');
    }

    // Relasi pesan WhatsApp untuk sesi ujian
    public function pesanWa(): HasOne
    {
        return $this->hasOne(PesanWa::class, 'sesi_ujian_id');
    }

    public function sisaDetik(): int
    {
        return max(
            0,
            $this->batas_selesai_at->getTimestamp() - now()->getTimestamp()
        );
    }

    public function berakhir(): bool
    {
        return $this->batas_selesai_at->getTimestamp()
            < now()->getTimestamp();
    }

    public function selesaikan(): void
    {
        if ($this->status === 'selesai') {
            return;
        }

        $this->update([
            'status' => 'selesai',
            'selesai_at' => now()->min($this->batas_selesai_at),
        ]);

        // Sesi yatim (ujiannya sudah dihapus) cukup ditutup, tidak dinilai
        if ($this->ujian) {
            Penilai::nilai($this);
        }
    }

    /**
     * Tutup sesi bila waktunya habis, ujian sudah tidak aktif, atau ujiannya sudah dihapus.
     * true = sesi baru saja ditutup.
     */
    public function periksaKedaluwarsa(): bool
    {
        if ($this->status !== 'berlangsung') {
            return false;
        }

        if (
            ! $this->ujian
            || $this->berakhir()
            || $this->ujian->status !== 'aktif'
        ) {
            $this->selesaikan();

            return true;
        }

        return false;
    }

    public function log(): HasMany
    {
        return $this->hasMany(
            LogPelanggaran::class,
            'sesi_ujian_id'
        );
    }
}