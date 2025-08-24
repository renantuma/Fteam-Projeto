<?php
// app/Http/Middleware/IntegrationMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IntegrationMiddleware
{
    private const HEADER_CLIENT_ID = 'X-Client-Id';
    private const RATE_LIMIT_REQUESTS = 100; 
    
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $requestId = $this->generateRequestId();
        $request->attributes->set('request_id', $requestId);

        
        $clientId = $request->header(self::HEADER_CLIENT_ID);
        if (!$clientId) {
            $this->logRequest($request, 400, 0, $requestId, 'Missing X-Client-Id header');
            return response()->json([
                'error' => 'Header X-Client-Id é obrigatório',
                'request_id' => $requestId
            ], 400);
        }

        
        if ($this->isRateLimited($clientId)) {
            $this->logRequest($request, 429, 0, $requestId, 'Rate limit exceeded');
            return response()->json([
                'error' => 'Rate limit excedido. Máximo ' . self::RATE_LIMIT_REQUESTS . ' requests por minuto',
                'request_id' => $requestId
            ], 429);
        }

        $this->logRequest($request, null, 0, $requestId, 'Request started', [
            'client_id' => $clientId,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $response = $next($request);
        
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);
        
        $this->logRequest($request, $response->getStatusCode(), $responseTime, $requestId, 'Request completed');
        
        
        $response->headers->set('X-Request-Id', $requestId);
        $response->headers->set('X-Response-Time', $responseTime . 'ms');

        return $response;
    }

    private function generateRequestId(): string
    {
        return uniqid('req_', true);
    }

    private function isRateLimited(string $clientId): bool
    {
        $key = "rate_limit:{$clientId}";
        $requests = Cache::get($key, 0);
        
        if ($requests >= self::RATE_LIMIT_REQUESTS) {
            return true;
        }

        Cache::put($key, $requests + 1, now()->addMinute());
        return false;
    }

    private function logRequest(Request $request, ?int $statusCode, float $responseTime, string $requestId, string $message, array $context = []): void
    {
        Log::info($message, array_merge([
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
            'status_code' => $statusCode,
            'response_time_ms' => $responseTime,
            'timestamp' => now()->toISOString(),
        ], $context));
    }
}