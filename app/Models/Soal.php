<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Soal extends Model
{
    protected $table = 'soal';

    protected $fillable = ['mapel_id', 'guru_id', 'tipe', 'stimulus', 'gambar', 'pertanyaan', 'poin'];

    public const TIPE_LABEL = [
        'pg' => 'Pilihan Ganda',
        'pg_kompleks' => 'Pilihan Ganda Kompleks',
        'benar_salah' => 'Benar/Salah',
    ];

    public const POIN = [
        'pg' => 1,
        'pg_kompleks' => 2,
        'benar_salah' => 3,
    ];

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function opsi(): HasMany
    {
        return $this->hasMany(OpsiJawaban::class)->orderBy('id');
    }

    /** Pemakaian: $soal->gambar_url */
    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar ? route('media.soal', basename($this->gambar)) : null;
    }
}