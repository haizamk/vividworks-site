<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DesignCallbackProcessor;
use App\Support\DesignCallbackStore;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class DesignCallbackController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (! $request->isJson()) {
            return $this->invalid(['body' => 'Content-Type must be application/json.']);
        }

        try {
            $result = DesignCallbackProcessor::handle($request->getContent());
        } catch (QueryException $exception) {
            if (DesignCallbackStore::isJobIdConflict($exception)) {
                return $this->conflict('job_id is already assigned to another attempt.');
            }

            Log::error('design_callback_failed', ['exception' => $exception::class]);

            return $this->internal();
        } catch (Throwable $exception) {
            Log::error('design_callback_failed', ['exception' => $exception::class]);

            return $this->internal();
        }

        return response()->json($result->body, $result->status);
    }

    /**
     * @param  array<string, string>  $errors
     */
    private function invalid(array $errors): JsonResponse
    {
        return response()->json([
            'received' => false,
            'error_code' => 'invalid_payload',
            'message' => 'Callback payload is invalid.',
            'errors' => $errors,
        ], 422);
    }

    private function conflict(string $message): JsonResponse
    {
        return response()->json([
            'received' => false,
            'error_code' => 'identity_conflict',
            'message' => $message,
        ], 409);
    }

    private function internal(): JsonResponse
    {
        return response()->json([
            'received' => false,
            'error_code' => 'internal_error',
            'message' => 'Callback could not be stored',
        ], 500);
    }
}
