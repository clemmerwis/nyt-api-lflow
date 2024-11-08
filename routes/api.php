<?php

use App\Http\Controllers\Api\V1\BestSellersController;
use Illuminate\Support\Facades\Route;

Route::post('1/nyt/best-sellers', App\Http\Controllers\Api\V1\BestSellersController::class)
    ->name('api.v1.best-sellers');
