<?php

namespace Ondine\Middleware;

class CorsMiddleware
{
    public function handle($request)
    {
        // Allow all for simplicity — customize for production
            // Build CORS headers without sending them directly so caller (App) can handle sending.
            $headers = [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            ];

            if (($request->method ?? 'GET') === 'OPTIONS') {
                // Return a 204 Response with the CORS headers for preflight
                return new \Ondine\Response(204, null, $headers);
            }

            // For normal requests, attach headers to a Response-like array to let App merge them later.
            // Return null to continue processing; App will still respect response headers if middleware returns a Response.
            // But to ensure headers are applied, we store them on the request for App to pick up if needed.
            if (is_object($request)) {
                $request->attributes['cors_headers'] = $headers;
            }
            return null;
    }
}
