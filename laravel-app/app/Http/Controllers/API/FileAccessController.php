<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\FileUrlService;
use App\Services\StorageService;
use Illuminate\Http\Request;

class FileAccessController extends Controller
{
    /**
     * Stream private files via temporary signed route.
     * Middleware 'signed' handles HMAC verification prior to this action.
     */
    public function show(Request $request)
    {
        $path = (string) $request->query('path', '');

        if (empty($path)) {
            abort(400, 'Missing file path');
        }

        if (!FileUrlService::isValidPath($path)) {
            abort(403, 'Unauthorized file path');
        }

        if (!StorageService::exists($path)) {
            abort(404, 'File not found');
        }

        $response = StorageService::response($path);
        $response->headers->set('Cache-Control', 'private, max-age=1800');

        return $response;
    }
}
