<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Setting\Services\FeatureToggleService;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $featureToggleService = app(FeatureToggleService::class);
        $path = $request->path();

        // Daftar mapping prefix path ke module key
        // Hanya memetakan modul yang bisa ditoggle (bisnis & core)
        $modulesMap = [
            'api/finance' => 'finance',
            'api/maintenance' => 'maintenance',
            'api/guest' => 'guest',
            'api/inventory' => 'inventory',
            'api/notification' => 'notification',
            'api/room' => 'room',
            'api/schedule' => 'schedule',
        ];

        foreach ($modulesMap as $prefix => $moduleKey) {
            // Jika request path dimulai dengan prefix modul
            if (str_starts_with($path, $prefix)) {
                // Cek apakah modul aktif menggunakan FeatureToggleService
                if (!$featureToggleService->isEnabled($moduleKey)) {
                    return response()->json([
                        'message' => 'Modul ' . ucfirst($moduleKey) . ' sedang dinonaktifkan oleh administrator.'
                    ], Response::HTTP_FORBIDDEN);
                }
                break; // Hanya perlu cek satu prefix terpanjang/pertama yang match
            }
        }

        return $next($request);
    }
}
