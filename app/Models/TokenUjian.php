<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenUjian extends Model
{
    protected $table = 'token_ujian';

    protected $fillable = ['ujian_id', 'user_id', 'token', 'dipakai_at'];

    protected function casts(): array
    {
        return ['dipakai_at' => 'datetime'];
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Kode acak 6 karakter, tanpa 0/O dan 1/I/L, dan dijamin belum dipakai */
    public static function buatKode(): string
    {
        $huruf = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        do {
            $kode = '';
            for ($i = 0; $i < 6; $i++) {
                $kode .= $huruf[random_int(0, strlen($huruf) - 1)];
            }
        } while (static::where('token', $kode)->exists());

        return $kode;
    }
}