<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TandaTangan
{
    private const AWALAN = 'data:image/png;base64,';

    /** Ubah data URL PNG menjadi biner. Mengembalikan null bila tidak valid. */
    public static function baca(?string $dataUrl): ?string
    {
        if (! is_string($dataUrl) || strlen($dataUrl) > 400000 || ! str_starts_with($dataUrl, self::AWALAN)) {
            return null;
        }

        $biner = base64_decode(substr($dataUrl, strlen(self::AWALAN)), true);

        if ($biner === false || strncmp($biner, "\x89PNG\r\n\x1a\n", 8) !== 0) {
            return null;
        }

        $info = @getimagesizefromstring($biner);

        if (! $info || $info[2] !== IMAGETYPE_PNG
            || $info[0] < 20 || $info[0] > 3000
            || $info[1] < 10 || $info[1] > 1500) {
            return null;
        }

        return $biner;
    }

    /** Simpan biner PNG ke folder privat, kembalikan path relatif. */
    public static function tulis(string $biner, string $folder): string
    {
        $path = trim($folder, '/').'/'.Str::random(40).'.png';
        Storage::disk('local')->put($path, $biner);

        return $path;
    }

    public static function simpan(?string $dataUrl, string $folder): ?string
    {
        $biner = self::baca($dataUrl);

        return $biner === null ? null : self::tulis($biner, $folder);
    }

    /** Untuk ditampilkan di halaman tanpa perlu alamat publik. */
    public static function dataUri(?string $path): ?string
    {
        if (! $path || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        return self::AWALAN.base64_encode(Storage::disk('local')->get($path));
    }

    public static function hapus(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }
}