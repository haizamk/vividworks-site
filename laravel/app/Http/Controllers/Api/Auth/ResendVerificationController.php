<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResendVerificationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'E-mel sudah disahkan.']);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Pautan pengesahan dihantar semula.']);
    }
}