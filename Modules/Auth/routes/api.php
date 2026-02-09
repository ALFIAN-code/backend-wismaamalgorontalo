<?php

use Modules\Auth\Models\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AdminPermissionController;
use Modules\Auth\Http\Controllers\AdminRoleController;
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
        Route::get('/permissions', [AdminPermissionController::class, 'index'])->middleware('permission:view-permission|create:roles|update-roles');
        Route::post('/permissions', [AdminPermissionController::class, 'store'])->middleware('permission:create-permission');
        Route::put('/permissions/{id}', [AdminPermissionController::class, 'update'])->middleware('permission:update-permission');
        Route::delete('/permissions/{id}', [AdminPermissionController::class, 'destroy'])->middleware('permission:delete-permission');

        Route::get('/roles', [AdminRoleController::class, 'index'])->middleware('permission:view-roles');
        Route::post('/roles', [AdminRoleController::class, 'store'])->middleware('permission:create-roles');
        Route::get('/roles/{role}', [AdminRoleController::class, 'show'])->middleware('permission:view-roles');
        Route::put('/roles/{role}', [AdminRoleController::class, 'update'])->middleware('permission:update-roles');
        Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy'])->middleware('permission:delete-roles');
    });
});
