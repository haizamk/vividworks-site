<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, string $id, string $hash): JsonResponse
    {
        if (! $request->hasValidSignature()) {
            return response()->json(['message' => 'Tandatangan pautan tidak sah atau tamat tempoh.'], 403);
        }

        $user = User::query()->findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Hash pengesahan tidak sah.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'E-mel sudah disahkan.']);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'E-mel disahkan.']);
    }
}