<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kehadiran extends Model
{
    protected $table = 'kehadiran';

    protected $fillable = ['ujian_id', 'user_id', 'hadir', 'keterangan', 'ttd_peserta', 'ttd_at'];

    protected function casts(): array
    {
        return [
            'hadir' => 'boolean',
            'ttd_at' => 'datetime',
        ];
    }
}