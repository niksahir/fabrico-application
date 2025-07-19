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
    MachineController
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
});

Route::middleware(['auth:sanctum', 'role:admin,user'])->group(function () {
    Route::apiResource('tickets', TicketController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('documents', DocumentController::class);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('quotations', QuotationController::class);
    //Route::apiResource('purchases', PurchaseController::class);
    Route::apiResource('staff', StaffController::class);

    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
});

Route::middleware(['auth:sanctum', 'user'])->group(function () {
    Route::apiResource('machines', MachineController::class);
});

