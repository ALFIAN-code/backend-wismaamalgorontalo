<?php

use Modules\Auth\Models\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;
use Modules\Auth\Http\Controllers\PermissionController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/permissions', [AuthController::class, 'myPermissions']);

    Route::prefix('admin')->group(function () {
        // route crud permission
        Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:view-permission');
        Route::post('/permissions', [PermissionController::class, 'store'])->middleware('permission:create-permission');
        Route::put('/permissions/{id}', [PermissionController::class, 'update'])->middleware('permission:edit-permission');
        Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->middleware('permission:delete-permission');
    });
});
