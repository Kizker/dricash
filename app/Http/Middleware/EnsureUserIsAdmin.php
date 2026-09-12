<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses ditolak. Khusus Administrator.',
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Halaman ini hanya dapat diakses oleh Administrator.');
        }

        if (! $user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun administrator Anda sedang dinonaktifkan.',
            ]);
        }

        return $next($request);
    }
}
