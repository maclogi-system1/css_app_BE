<?php

use App\Http\Controllers\Api\ShopController;
use Illuminate\Support\Facades\Route;

Route::prefix('shops')->name('shops.')->controller(ShopController::class)->group(function () {
    Route::get('/', 'index')->name('index');
});
