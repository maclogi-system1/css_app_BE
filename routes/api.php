<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\UpdateUserProfileInformationController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\ChatworkController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/send-password-reset-link', [PasswordController::class, 'sendPasswordResetLink'])
    ->name('send-password-reset-link');
Route::post('/password-reset-token', [PasswordController::class, 'getPasswordResetToken'])
    ->name('password-reset-token');
Route::post('/reset-password', [PasswordController::class, 'reset'])
    ->name('reset-password');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/verify-company', [LoginController::class, 'verifyCompany'])->name('verify-company');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/', function (Request $request) {
            return $request->user();
        })->name('info');
        Route::put('/', [UpdateUserProfileInformationController::class, 'update'])->name('update-user-profile-info');
        Route::patch('/update-password', [PasswordController::class, 'update'])->name('update-password');
        Route::post('/upload-photo', [UpdateUserProfileInformationController::class, 'uploadProfilePhoto'])
            ->name('update-profile-photo');
    });

    Route::delete('/users/delete-multiple', [UserController::class, 'deleteMultiple'])->name('users.delete-multiple');
    Route::apiResource('users', UserController::class);

    Route::apiResource('roles', RoleController::class);

    Route::prefix('bookmarks')->name('bookmarks.')->group(function () {
        Route::get('/', [BookmarkController::class, 'index'])->name('index');
        Route::post('/bookmark', [BookmarkController::class, 'bookmark'])->name('bookmark');
        Route::post('/unbookmark', [BookmarkController::class, 'unbookmark'])->name('unbookmark');
    });

    Route::apiResource('companies', CompanyController::class);
    Route::patch('/companies/{id}/restore', [CompanyController::class, 'restore'])->name('companies.restore');
    Route::delete('/companies/{id}/force', [CompanyController::class, 'forceDelete'])->name('companies.force-delete');

    Route::get('/user-settings', [UserSettingController::class, 'index'])->name('user-settings.index');
    Route::patch('/user-settings', [UserSettingController::class, 'update'])->name('user-settings.update');

    Route::prefix('chatwork')->name('chatwork.')->group(function () {
        Route::get('/room/{roomId}', [ChatworkController::class, 'roomDetail'])->name('room.detail');
        Route::get('/room/{roomId}/members', [ChatworkController::class, 'roomMembers'])->name('room.members');
        Route::post('/send-message/{roomId}', [ChatworkController::class, 'sendMessage'])->name('send-message');
    });

    Route::get('/teams/company/{company}', [TeamController::class, 'getListByCompany'])
        ->name('teams.get-list-by-company');
    Route::apiResource('teams', TeamController::class);
});
