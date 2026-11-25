<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $polos = $request->bearerToken();

        if (! $polos) {
            return $this->tolak();
        }

        $token = ApiToken::with('user')
            ->where('hash', hash('sha256', $polos))
            ->where('kedaluwarsa_at', '>', now())
            ->first();

        $user = $token?->user;

        if (! $user || ! $user->is_active || $user->role !== User::ROLE_SISWA) {
            return $this->tolak();
        }

        // Catat aktivitas paling sering sekali per menit
        if (! $token->terakhir_dipakai || $token->terakhir_dipakai->lt(now()->subMinute())) {
            $token->forceFill(['terakhir_dipakai' => now()])->save();
        }

        $request->setUserResolver(fn () => $user);
        $request->attributes->set('api_token', $token);

        return $next($request);
    }

    private function tolak(): Response
    {
        return response()->json(['pesan' => 'Sesi login berakhir. Silakan login kembali.'], 401);
    }
}