<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds standard browser security headers to every web response.
 *
 * The page loads nothing from other sites, so the content security policy
 * only allows this site's own files. Its two inline scripts carry a
 * per-request nonce; any other script is blocked.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // A fresh random nonce for this request, read by the view.
        Vite::useCspNonce();

        $response = $next($request);

        $policy = implode('; ', [
            "default-src 'self'",
            "script-src 'nonce-".Vite::cspNonce()."'",
            // Inline style attributes are used throughout the page.
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]);

        $headers = [
            'Content-Security-Policy' => $policy,
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
        ];

        // Tell browsers to always use HTTPS. Only in production, so local
        // development addresses are not affected.
        if (app()->isProduction()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000';
        }

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
}
