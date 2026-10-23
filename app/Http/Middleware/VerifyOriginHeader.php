<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses requests that did not come through NextStep's CloudFront
 * distribution, by checking the secret header CloudFront adds.
 *
 * Does nothing until a secret is configured (config/origin.php). Runs
 * before sessions start, so refused requests get no cookies and never
 * reach the demos or the AI.
 */
class VerifyOriginHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $accepted = array_values(array_filter([
            config('origin.secret'),
            config('origin.previous_secret'),
        ], fn ($secret) => is_string($secret) && $secret !== ''));

        if ($accepted === [] || in_array($request->path(), config('origin.exempt_paths'), true)) {
            return $next($request);
        }

        $sent = (string) $request->header(config('origin.header'), '');

        foreach ($accepted as $secret) {
            // Constant-time comparison, so response timing reveals nothing.
            if ($sent !== '' && hash_equals($secret, $sent)) {
                return $next($request);
            }
        }

        // Deliberately plain: no hint about what is missing.
        return response('Forbidden', 403, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
