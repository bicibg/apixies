<?php

use App\Http\Controllers\ServiceInfoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAuthController;

Route::get('/', [ServiceInfoController::class, 'getApiRoutes'])->name('getApiRoutes');
Route::get('/register', [WebAuthController::class, 'showRegister']);
Route::get('/login',    [WebAuthController::class, 'showLogin']);

Route::prefix('api')
    ->middleware('api')
    ->group(base_path('routes/api.php'));

