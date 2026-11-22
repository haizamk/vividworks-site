<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectOversizedJson
{
    public const MAX_BYTES = 20 * 1024;

    /**
     * Match the Express JSON parser limit of 20kb.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // The design callback has its own raw-body limit. Contact and every
        // other API route keep this 20KB plain-text response.
        if ($request->is('api/ai/design-callback')) {
            return $next($request);
        }

        $declared = $request->headers->get('Content-Length');
        $declaredLength = is_numeric($declared) ? (int) $declared : 0;

        if ($declaredLength > self::MAX_BYTES || strlen($request->getContent()) > self::MAX_BYTES) {
            return response('Payload Too Large', 413, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        return $next($request);
    }
}
