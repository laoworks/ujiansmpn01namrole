<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiToken extends Model
{
    protected $table = 'api_token';

    protected $fillable = ['user_id', 'hash', 'perangkat', 'terakhir_dipakai', 'kedaluwarsa_at'];

    protected function casts(): array
    {
        return [
            'terakhir_dipakai' => 'datetime',
            'kedaluwarsa_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}