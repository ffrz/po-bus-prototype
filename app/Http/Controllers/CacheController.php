<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class CacheController extends Controller
{
    // Token simple agar tidak sembarang orang akses
    private function authorize(Request $request)
    {
        return $request->query('token') === env('APP_WEB_CMD_TOKEN');
    }

    public function clear(Request $request)
    {
        if (! $this->authorize($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('event:clear');
        Artisan::call('cache:clear');

        return response()->json(['status' => 'Caches cleared']);
    }

    public function cache(Request $request)
    {
        if (! $this->authorize($request)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        Artisan::call('event:cache');

        return response()->json(['status' => 'Caches created']);
    }
}
