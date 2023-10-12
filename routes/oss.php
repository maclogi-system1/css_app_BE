<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('shops')->name('shops.')->controller(ShopController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/shop-options', 'getOptions')->name('shop-options');
    Route::get('/{storeId}', 'show')->name('show');
    Route::put('/{storeId}', 'update')->name('update');
    Route::post('/', 'create')->name('create');
});

Route::prefix('alerts')->name('alerts.')->controller(AlertController::class)->group(function () {
    Route::get('/', 'index')->name('index');
});

Route::prefix('tasks')->name('tasks.')->controller(TaskController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/options', 'getOptions')->name('get-options');
    Route::get('/download-template', 'downloadTemplateCsv')->name('download-template');
    Route::post('/{storeId}', 'storeMultiple')->name('store-multiple');
    Route::put('/{storeId}', 'updateMultiple')->name('update-multiple');
    Route::delete('/{storeId}/{taskId}', 'delete')->name('delete');
});
