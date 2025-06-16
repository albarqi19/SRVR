<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $corsConfig = config('cors');
        $origin = $request->headers->get('Origin');

        // Handle preflight requests
        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }

        // Check if origin is allowed
        $allowedOrigins = $corsConfig['allowed_origins'] ?? [];
        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        // Set CORS headers
        $response->headers->set('Access-Control-Allow-Methods', implode(', ', $corsConfig['allowed_methods'] ?? ['*']));
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', $corsConfig['allowed_headers'] ?? ['*']));
        
        if ($corsConfig['supports_credentials'] ?? false) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        if (isset($corsConfig['max_age']) && $corsConfig['max_age'] > 0) {
            $response->headers->set('Access-Control-Max-Age', $corsConfig['max_age']);
        }

        if (!empty($corsConfig['exposed_headers'])) {
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', $corsConfig['exposed_headers']));
        }

        return $response;
    }
}
