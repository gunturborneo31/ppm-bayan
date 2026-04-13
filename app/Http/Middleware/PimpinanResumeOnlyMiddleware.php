<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PimpinanResumeOnlyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !$user->isPimpinan()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $allowedRoutePatterns = [
            'resume.*',
            'logout',
        ];

        foreach ($allowedRoutePatterns as $pattern) {
            if (Str::is($pattern, (string) $routeName)) {
                return $next($request);
            }
        }

        return redirect()->route('resume.pilar.index');
    }
}
