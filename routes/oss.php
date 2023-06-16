<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\ShopController;
use Illuminate\Support\Facades\Route;

Route::prefix('shops')->name('shops.')->controller(ShopController::class)->group(function () {
    Route::get('/', 'index')->name('index');
});

Route::prefix('alerts')->name('alerts.')->controller(AlertController::class)->group(function () {
    Route::get('/', 'index')->name('index');
});
