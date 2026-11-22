<?php

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
Route::post('/contact', ContactController::class)->middleware(LimitContactRequests::class);
Route::get('/inquiries', InquiryController::class);
Route::post('/ai/design-generations', DesignGenerationController::class);
Route::post('/ai/design-callback', DesignCallbackController::class)->middleware([
    AuthenticateDesignCallback::class,
    RejectOversizedDesignCallback::class,
]);
