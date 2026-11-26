<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PenjagaPerangkat
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Hanya siswa yang dibatasi, dan hanya kalau penjaga dinyalakan
        if (! $user || $user->role !== User::ROLE_SISWA || ! config('ujian.penjaga')) {
            return $next($request);
        }

        if (self::periksa($request)['sah']) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['pesan' => 'Perangkat tidak diizinkan.'], 403);
        }

        return response()->view('perangkat.ditolak', [], 403);
    }

    /**
     * Jenis klien untuk request ini: 'app', 'seb', 'browser', atau 'lain'.
     * Dipakai controller untuk menentukan batas pelanggaran.
     */
    public static function klien(Request $request): string
    {
        $p = self::periksa($request);

        return $p['kode'] ?? ($p['browser']['mirip'] ? 'browser' : 'lain');
    }

    /** Hasil pemeriksaan lengkap (dipakai juga oleh halaman /cek-perangkat) */
    public static function periksa(Request $request): array
    {
        $url = explode('#', $request->fullUrl())[0];
        $ua = (string) $request->userAgent();

        $kunciApp = (string) config('ujian.kunci_app');
        $app = ['ada' => false, 'dikonfigurasi' => $kunciApp !== '', 'sah' => false];

        if (preg_match('/UjianApp\/[\d.]+ KUNCI\/(\S+)/', $ua, $m)) {
            $app['ada'] = true;
            $app['sah'] = $kunciApp !== '' && hash_equals($kunciApp, $m[1]);
        }

        $config = self::cocok($url, $request->header('X-SafeExamBrowser-ConfigKeyHash'), config('ujian.seb_config_key'));
        $bek = self::cocok($url, $request->header('X-SafeExamBrowser-RequestHash'), config('ujian.seb_exam_key'));

        // Aplikasi ujian dan SEB yang gagal verifikasi kunci tidak boleh lolos sebagai browser biasa.
        $menyamarApp = (bool) preg_match('/(UjianApp|SEB)\//', $ua);

        // Semua HP/tablet: Android, iPhone, iPad, iPod, HarmonyOS, dan browser apa pun di dalamnya
        // (Chrome, Samsung Internet, Firefox, Edge, Opera, UC, Via, WebView, dll).
        $seluler = (bool) preg_match('/(Android|iPhone|iPad|iPod|Mobile|HarmonyOS|Windows Phone)/i', $ua);

        // Chrome asli di desktop/laptop (Windows, macOS, Linux, ChromeOS)
        $chromeDesktop = (bool) preg_match('/(Chrome|CriOS)\//', $ua)
            && ! preg_match('/(Edg|EdgA|EdgiOS|OPR|Opera|SamsungBrowser)\//', $ua);

        $mirip = ! $menyamarApp && ($seluler || $chromeDesktop);

        $izinkan = (bool) config('ujian.izinkan_browser');

        $browser = [
            'diizinkan' => $izinkan,
            'seluler' => $seluler,
            'mirip' => $mirip,
            'sah' => $izinkan && $mirip,
        ];

        $jenis = null;
        $kode = null;

        if ($app['sah']) {
            $jenis = 'Aplikasi Android';
            $kode = 'app';
        } elseif ($config['sah'] || $bek['sah']) {
            $jenis = 'Safe Exam Browser';
            $kode = 'seb';
        } elseif ($browser['sah']) {
            $jenis = $seluler ? 'Browser HP' : 'Google Chrome';
            $kode = 'browser';
        }

        return [
            'url' => $url,
            'ua' => $ua,
            'app' => $app,
            'seb_config' => $config,
            'seb_bek' => $bek,
            'browser' => $browser,
            'sah' => $jenis !== null,
            'jenis' => $jenis,
            'kode' => $kode,
        ];
    }

    /** Bandingkan header SEB dengan sha256(URL + kunci) untuk setiap kunci yang terdaftar */
    private static function cocok(string $url, ?string $header, ?string $kunci): array
    {
        $header = strtolower(trim((string) $header));
        $daftar = array_values(array_filter(array_map('trim', explode(',', (string) $kunci))));
        $sah = false;

        if ($header !== '') {
            foreach ($daftar as $k) {
                foreach (array_unique([$k, strtolower($k)]) as $varian) {
                    if (hash_equals(hash('sha256', $url.$varian), $header)) {
                        $sah = true;
                    }
                }
            }
        }

        return ['ada' => $header !== '', 'dikonfigurasi' => $daftar !== [], 'sah' => $sah];
    }
}