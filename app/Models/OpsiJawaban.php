<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpsiJawaban extends Model
{
    protected $table = 'opsi_jawaban';

    protected $fillable = ['soal_id', 'kode', 'teks', 'gambar', 'is_benar'];

    protected function casts(): array
    {
        return ['is_benar' => 'boolean'];
    }

    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar ? route('media.soal', basename($this->gambar)) : null;
    }
}