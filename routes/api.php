<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\API\{
    TicketController,
    QuotationController,
    PurchaseController,
    DocumentController,
    StaffController,
    UserController,
    DashboardController,
    ActivityLogController
};
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // You’ll add other protected routes here later (tickets, users, etc.)
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('tickets', TicketController::class);
    //Route::apiResource('ticket-attachments', TicketAttachmentController::class);
    Route::apiResource('quotations', QuotationController::class);
    //Route::apiResource('quotation-attachments', QuotationAttachmentController::class);
    //Route::apiResource('purchases', PurchaseController::class);
    Route::apiResource('documents', DocumentController::class);
    Route::apiResource('staff', StaffController::class);
    Route::apiResource('users', UserController::class);

    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('dashboard/activities', [ActivityLogController::class, 'index']);
});
