<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AdminPermissionController;
use Modules\Auth\Http\Controllers\AdminRoleController;
use Modules\Auth\Http\Controllers\AdminUserController;
use Modules\Auth\Http\Controllers\AuthController;
use Modules\Auth\Http\Controllers\PermissionController;
use Modules\Auth\Models\Permission;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/permissions', [AuthController::class, 'myPermissions']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::prefix('admin')->group(function () {
        // route crud permission
        Route::get('/permissions', [AdminPermissionController::class, 'index'])->middleware('permission:view-permission');
        Route::post('/permissions', [AdminPermissionController::class, 'store'])->middleware('permission:create-permission');
        Route::get('/permissions/{id}', [AdminPermissionController::class, 'show'])->middleware('permission:view-permission');
        Route::put('/permissions/{id}', [AdminPermissionController::class, 'update'])->middleware('permission:update-permission');
        Route::delete('/permissions/{id}', [AdminPermissionController::class, 'destroy'])->middleware('permission:delete-permission');

        // route crud role
        Route::get('/roles', [AdminRoleController::class, 'index'])->middleware('permission:view-role');
        Route::post('/roles', [AdminRoleController::class, 'store'])->middleware('permission:create-role');
        Route::get('/roles/{role}', [AdminRoleController::class, 'show'])->middleware('permission:view-role');
        Route::put('/roles/{role}', [AdminRoleController::class, 'update'])->middleware('permission:update-role');
        Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy'])->middleware('permission:delete-role');

        Route::get('roles-options', [AdminUserController::class, 'getRoles']);

        // route crud user
        Route::get('/users', [AdminUserController::class, 'index'])->middleware('permission:view-user');
        Route::post('/users', [AdminUserController::class, 'store'])->middleware('permission:create-user');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->middleware('permission:view-user');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->middleware('permission:update-user');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->middleware('permission:delete-user');
    });
});
