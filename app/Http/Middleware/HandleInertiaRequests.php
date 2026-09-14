<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Handle the incoming request and harden response headers against caching issues.
     */
    public function handle(Request $request, \Closure $next): \Symfony\Component\HttpFoundation\Response
    {
        $response = parent::handle($request, $next);

        // Prevent browser disk cache, mobile PWA standalone cache, LiteSpeed, and CDNs
        // from caching dynamic responses (especially Inertia JSON).
        // This ensures opening the PWA never renders raw cached JSON.
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        // Ensure Vary header preserves both X-Inertia and Accept headers
        $response->headers->set('Vary', 'X-Inertia, Accept', false);

        // Explicitly bypass LiteSpeed Cache & Nginx/CDN proxy cache
        $response->headers->set('X-LiteSpeed-Cache-Control', 'no-cache, no-store');
        $response->headers->set('X-Accel-Expires', '0');

        return $response;
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $now = Carbon::now();
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'user',
                    'is_admin' => ($user->role ?? 'user') === 'admin',
                    'is_active' => (bool) ($user->is_active ?? true),
                    'currency' => $user->currency ?? 'IDR',
                    'monthly_start_day' => $user->monthly_start_day ?? 1,
                    'initial_net_worth' => (float) ($user->initial_net_worth ?? 0),
                    'daily_budget_mode' => $user->daily_budget_mode ?? 'auto',
                    'manual_daily_budget' => (float) ($user->manual_daily_budget ?? 0),
                    'avatar_url' => $user->avatar_url,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'intervention' => fn () => $request->session()->get('intervention'),
            ],
            'current_period' => [
                'month' => (int) $now->format('m'),
                'year' => (int) $now->format('Y'),
                'month_name' => $now->translatedFormat('F Y'),
                'day' => (int) $now->format('d'),
                'days_in_month' => $now->daysInMonth,
                'days_remaining' => max(1, $now->daysInMonth - (int) $now->format('d') + 1),
            ],
            'csrf_token' => csrf_token(),
        ];
    }
}
