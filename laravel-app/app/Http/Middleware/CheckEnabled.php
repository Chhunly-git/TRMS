<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return response([
                'message' => 'Unauthorized. Please login.'
            ], 401);
        }

        $status = strtoupper($user->status ?? '');
        if (in_array($status, ['DISABLED', 'SUSPENDED', 'INACTIVE'])) {
            return response([
                'message' => 'Unauthorized. Account is disabled.'
            ], 403);
        }

        return $next($request);
    }
}