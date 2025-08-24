<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SimpleRateLimit
{
    public function handle(Request $request, Closure $next, $maxAttempts = 10, $decayMinutes = 1)
    {
        
        $clientId = $request->header('X-Client-Id');
        $key = 'rate_limit:' . ($clientId ?: $request->ip());
        
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            return response()->json([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Please try again in ' . $decayMinutes . ' minutes.',
                'retry_after' => $decayMinutes * 60,
                'max_attempts' => (int) $maxAttempts,
                'decay_minutes' => (int) $decayMinutes
            ], 429);
        }

        
        Cache::put($key, $attempts + 1, $decayMinutes * 60);

       
        $response = $next($request);
        
        if ($response instanceof Response) {
            $response->headers->set('X-RateLimit-Limit', $maxAttempts);
            $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - ($attempts + 1)));
            $response->headers->set('X-RateLimit-Reset', time() + ($decayMinutes * 60));
        }

        return $response;
    }
}