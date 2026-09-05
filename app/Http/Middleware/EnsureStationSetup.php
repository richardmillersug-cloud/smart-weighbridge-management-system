<?php

namespace App\Http\Middleware;

use App\Support\StationSetupState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStationSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        if (StationSetupState::isComplete()) {
            if ($request->routeIs('setup.*')) {
                return redirect()->route('login');
            }

            return $next($request);
        }

        if ($request->routeIs('setup.*')) {
            return $next($request);
        }

        return redirect()->route('setup.show');
    }
}
