<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Belum login: JSON mendapat 401, halaman web diarahkan ke login
        if (! $user) {
            if ($request->expectsJson()) {
                abort(401, 'Silakan login terlebih dahulu.');
            }

            return redirect()->guest(route('login'));
        }

        // Akun dinonaktifkan saat sedang login: keluarkan paksa
        if (! $user->is_active) {
            Auth::guard()->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                abort(403, 'Akun Anda telah dinonaktifkan.');
            }

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Akun Anda telah dinonaktifkan.']);
        }

        // Tanpa parameter role: cukup login dan aktif
        if ($roles !== [] && ! $user->hasRole(...$roles)) {
            abort(403, 'Anda tidak punya akses ke halaman ini.');
        }

        return $next($request);
    }
}