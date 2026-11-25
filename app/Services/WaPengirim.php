<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WaPengirim
{
    /**
     * @return array{ok: bool, galat: ?string, ulang: bool, koneksi: bool}
     * ulang = layak dicoba lagi nanti, koneksi = masalahnya jaringan
     */
    public static function kirim(string $nomor, string $isi): array
    {
        return match (config('ujian.wa.driver')) {
            'fonnte' => self::fonnte($nomor, $isi),
            default => self::percobaan($nomor, $isi),
        };
    }

    /** Mode percobaan: pesan hanya ditulis ke file, tidak dikirim ke siapa pun. */
    private static function percobaan(string $nomor, string $isi): array
    {
        file_put_contents(
            storage_path('logs/wa-percobaan.log'),
            '[' . now()->toDateTimeString() . "] KE {$nomor}\n{$isi}\n------------------------------\n",
            FILE_APPEND | LOCK_EX
        );

        return [
            'ok' => true,
            'galat' => null,
            'ulang' => false,
            'koneksi' => false,
        ];
    }

    private static function fonnte(string $nomor, string $isi): array
    {
        $token = (string) config('ujian.wa.fonnte_token');

        if ($token === '') {
            return self::gagal(
                'Token Fonnte belum diisi (WA_FONNTE_TOKEN di .env).',
                false
            );
        }

        try {
            $permintaan = Http::withHeaders([
                'Authorization' => $token,
            ]);

            // Tunjuk langsung ke cacert.pem bila ditemukan
            // (tidak bergantung pada php.ini).
            if ($ca = self::caBundle()) {
                $permintaan = $permintaan->withOptions([
                    'verify' => $ca,
                ]);
            }

            $r = $permintaan
                ->asForm()
                ->connectTimeout(4)
                ->timeout(20)
                ->post('https://api.fonnte.com/send', [
                    'target' => $nomor,
                    'message' => $isi,
                    'countryCode' => '62',
                ]);
        } catch (ConnectionException $e) {
            if (
                str_contains($e->getMessage(), 'error 60') ||
                str_contains($e->getMessage(), 'SSL certificate')
            ) {
                Log::warning('WA SSL gagal: ' . $e->getMessage());

                return self::gagal(
                    'Sertifikat SSL PHP belum diatur (lihat panduan cacert.pem).',
                    false
                );
            }

            return self::gagal(
                'Tidak ada koneksi internet ke layanan WhatsApp.',
                true,
                true
            );
        } catch (\Throwable $e) {
            return self::gagal(
                Str::limit('Gagal: ' . $e->getMessage(), 200, ''),
                true
            );
        }

        $json = $r->json();

        if (
            $r->successful() &&
            is_array($json) &&
            ($json['status'] ?? false) === true
        ) {
            return [
                'ok' => true,
                'galat' => null,
                'ulang' => false,
                'koneksi' => false,
            ];
        }

        $alasan = is_array($json)
            ? (string) ($json['reason'] ?? $json['detail'] ?? '')
            : '';

        if ($alasan === '') {
            $alasan = 'Respons HTTP ' . $r->status();
        }

        // Token atau nomor yang salah tidak akan membaik dengan mengulang.
        $tetap = str_contains(strtolower($alasan), 'invalid')
            || str_contains(strtolower($alasan), 'required');

        return self::gagal(
            Str::limit($alasan, 200, ''),
            ! $tetap
        );
    }

    /**
     * Cari file cacert.pem yang valid.
     * Mengembalikan null jika tidak ada, sehingga pengaturan bawaan
     * PHP/cURL yang dipakai.
     */
    private static function caBundle(): ?string
    {
        $kandidat = [
            config('ujian.wa.ca_bundle'),
            ini_get('curl.cainfo'),
            ini_get('openssl.cafile'),
            dirname(PHP_BINARY) . '/extras/ssl/cacert.pem',
            storage_path('app/cacert.pem'),
        ];

        foreach ($kandidat as $path) {
            if (
                is_string($path) &&
                $path !== '' &&
                is_file($path)
            ) {
                return $path;
            }
        }

        return null;
    }

    private static function gagal(
        string $galat,
        bool $ulang,
        bool $koneksi = false
    ): array {
        return [
            'ok' => false,
            'galat' => $galat,
            'ulang' => $ulang,
            'koneksi' => $koneksi,
        ];
    }
}