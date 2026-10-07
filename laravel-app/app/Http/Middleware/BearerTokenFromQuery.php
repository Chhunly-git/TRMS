<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BearerTokenFromQuery
{
    /**
     * Handle an incoming request.
     * Extracts token from query parameter if Authorization header is missing.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->bearerToken() && $request->has('token')) {
            $token = $request->query('token');
            if (!empty($token)) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }
        }

        return $next($request);
    }
}
