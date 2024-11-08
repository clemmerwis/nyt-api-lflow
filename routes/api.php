<?php

use App\Http\Controllers\Api\V1\BestSellersController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/best-sellers', [BestSellersController::class, 'history']);
});
