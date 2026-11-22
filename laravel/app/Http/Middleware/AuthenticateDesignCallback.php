<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateDesignCallback
{
    /**
     * Reject a missing or wrong token before any generation lookup.
     * The token value is never logged or written into the response.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.vividai.callback_token', '');
        $provided = $request->headers->get('X-VividAI-Callback-Token');
        $provided = is_string($provided) ? $provided : '';

        if ($expected === '' || $provided === '' || ! hash_equals($expected, $provided)) {
            return response()->json([
                'received' => false,
                'error_code' => 'unauthorized',
                'message' => 'Invalid callback credentials',
            ], 401);
        }

        return $next($request);
    }
}
