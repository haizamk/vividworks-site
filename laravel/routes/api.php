<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResendVerificationController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DesignCallbackController;
use App\Http\Controllers\Api\DesignGenerationController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Middleware\AuthenticateDesignCallback;
use App\Http\Middleware\LimitContactRequests;
use App\Http\Middleware\RejectOversizedDesignCallback;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::post('/register', RegisterController::class)->middleware('throttle:6,1');
Route::post('/login', LoginController::class)->middleware('throttle:6,1');
Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', LogoutController::class);
    Route::post('/email/verification-notification', ResendVerificationController::class)
        ->middleware('throttle:6,1');
});
Route::post('/contact', ContactController::class)->middleware(LimitContactRequests::class);
Route::get('/inquiries', InquiryController::class);
Route::post('/ai/design-generations', DesignGenerationController::class);
Route::post('/ai/design-callback', DesignCallbackController::class)->middleware([
    AuthenticateDesignCallback::class,
    RejectOversizedDesignCallback::class,
]);
