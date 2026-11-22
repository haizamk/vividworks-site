<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectOversizedDesignCallback
{
    /**
     * Reject from the raw body before the controller decodes JSON.
     * A short Content-Length does not hide a longer body.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $limit = (int) config('services.vividai.callback_max_bytes', 20 * 1024);
        if ($limit < 1) {
            $limit = 20 * 1024;
        }

        $declared = $request->headers->get('Content-Length');
        $declaredLength = is_numeric($declared) ? (int) $declared : 0;
        $bodyLength = strlen($request->getContent());

        if ($declaredLength > $limit || $bodyLength > $limit) {
            return response()->json([
                'received' => false,
                'error_code' => 'payload_too_large',
                'message' => 'Callback payload is too large',
            ], 413);
        }

        return $next($request);
    }
}
