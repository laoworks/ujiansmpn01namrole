<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jawaban extends Model
{
    protected $table = 'jawaban';

    protected $fillable = ['sesi_ujian_id', 'soal_id', 'jawaban', 'ragu', 'is_benar', 'poin_didapat'];

    protected function casts(): array
    {
        return [
            'jawaban' => 'array',
            'ragu' => 'boolean',
            'is_benar' => 'boolean',
        ];
    }
}