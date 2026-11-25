<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KunciApp
{
    public function handle(Request $request, Closure $next): Response
    {
        $kunci = (string) config('ujian.kunci_app');
        $kiriman = (string) $request->header('X-Kunci-App');

        if ($kunci === '' || ! hash_equals($kunci, $kiriman)) {
            return response()->json(['pesan' => 'Aplikasi tidak dikenali.'], 403);
        }

        return $next($request);
    }
}