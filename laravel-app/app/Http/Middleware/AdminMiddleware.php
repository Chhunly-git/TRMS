<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
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
                'message' => 'Unauthorized.'
            ], 401);
        }

        // 1. ប្រសិនបើជា ADMIN មានសិទ្ធិពេញលេញលើគ្រប់មុខងារទាំងអស់
        if ($user->level === 'ADMIN') {
            return $next($request);
        }

        // 2. ពិនិត្យសិទ្ធិតាមផ្លូវ API (Path-based Permission Check)
        $path = $request->path();

        // ហាមឃាត់ដាច់ខាតមិនឱ្យ User ធម្មតាចូលកែប្រែសិទ្ធិ ឬលុប User ឡើយ
        if (str_contains($path, 'manage/users/permissions')) {
            return response([
                'message' => 'Unauthorized. Admin access required to manage permissions.'
            ], 403);
        }

        if ($this->hasPermissionForPath($user, $path, $request)) {
            return $next($request);
        }

        return response([
            'message' => 'Unauthorized. You do not have permission to access this resource.'
        ], 403);
    }

    /**
     * ផ្ទៀងផ្ទាត់ផ្លូវ URL ជាមួយសិទ្ធិរបស់ User
     */
    private function hasPermissionForPath($user, string $path, Request $request): bool
    {
        if (str_contains($path, 'manage/attendances')) {
            return $user->hasPermission('attendances');
        }

        if (str_contains($path, 'manage/document-templates')) {
            return $user->hasPermission('manage-document-templates');
        }

        if (str_contains($path, 'manage/departments')) {
            return $user->hasPermission('departments') 
                || ($request->isMethod('GET') && ($user->hasPermission('divisions') || $user->hasPermission('users')));
        }

        if (str_contains($path, 'manage/offices')) {
            return $user->hasPermission('divisions') 
                || ($request->isMethod('GET') && $user->hasPermission('users'));
        }

        if (str_contains($path, 'manage/positions')) {
            return $user->hasPermission('positions') 
                || ($request->isMethod('GET') && $user->hasPermission('users'));
        }

        if (str_contains($path, 'manage/users')) {
            return $user->hasPermission('users');
        }

        if (str_contains($path, 'manage/backups')) {
            return $user->hasPermission('backups');
        }

        if (str_contains($path, 'manage/dashboard')) {
            return $user->hasPermission('dashboard');
        }

        if (str_contains($path, 'manage/meeting-rooms')) {
            return $user->hasPermission('manage-meeting-rooms');
        }

        return false;
    }
}