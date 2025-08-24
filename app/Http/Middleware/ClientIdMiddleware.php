<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClientIdMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        if (!$request->hasHeader('X-Client-Id')) {
            return response()->json([
                'error' => 'X-Client-Id header is required'
            ], 400);
        }

        $response = $next($request);
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        Log::info('API Request', [
            'client_id' => $request->header('X-Client-Id'),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration
        ]);

        return $response;
    }
}