<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DesignGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonException;

/**
 * Starts one Design Agent generation.
 *
 * The marketing site does not start generation. This endpoint is the entry
 * point, and page_count is required here. There is no silent fallback.
 */
class DesignGenerationController extends Controller
{
    public function __invoke(Request $request, DesignGenerationService $service): JsonResponse
    {
        if (! $request->isJson()) {
            return response()->json([
                'received' => false,
                'error_code' => 'invalid_payload',
                'message' => 'Generation request is invalid.',
                'errors' => ['body' => 'Content-Type must be application/json.'],
            ], 422);
        }

        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return response()->json([
                'received' => false,
                'error_code' => 'invalid_payload',
                'message' => 'Generation request is invalid.',
                'errors' => ['body' => 'Body must be a JSON object.'],
            ], 422);
        }

        if (! is_array($payload) || array_is_list($payload)) {
            return response()->json([
                'received' => false,
                'error_code' => 'invalid_payload',
                'message' => 'Generation request is invalid.',
                'errors' => ['body' => 'Body must be a JSON object.'],
            ], 422);
        }

        $result = $service->generate($payload);

        return response()->json($result->body, $result->status);
    }
}
