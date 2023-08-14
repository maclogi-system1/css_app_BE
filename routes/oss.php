<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('shops')->name('shops.')->controller(ShopController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{storeId}', 'show')->name('show');
});

Route::prefix('alerts')->name('alerts.')->controller(AlertController::class)->group(function () {
    Route::get('/', 'index')->name('index');
});

Route::prefix('tasks')->name('tasks.')->controller(TaskController::class)->group(function () {
    Route::get('/', 'index')->name('index');
});
