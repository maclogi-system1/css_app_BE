<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\UpdateUserProfileInformationController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\ChatworkController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\PermissionController;
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

    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::delete('/delete-multiple', 'deleteMultiple')->name('delete-multiple');
        Route::get('/search', 'search')->name('search');
        Route::post('/{user}', 'update')->name('update');
    });
    Route::apiResource('users', UserController::class)->except(['update']);

    Route::get('/roles/search', [RoleController::class, 'search'])->name('roles.search');
    Route::apiResource('roles', RoleController::class);

    Route::prefix('permissions')->name('permissions.')->controller(PermissionController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/search', 'search')->name('search');
        Route::patch('/{permission}', 'update')->name('update');
    });

    Route::prefix('bookmarks')->name('bookmarks.')->controller(BookmarkController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/bookmark', 'bookmark')->name('bookmark');
        Route::post('/unbookmark', 'unbookmark')->name('unbookmark');
    });

    Route::prefix('companies')->name('companies.')->controller(CompanyController::class)->group(function () {
        Route::patch('/{id}/restore', 'restore')->name('restore');
        Route::delete('/{id}/force', 'forceDelete')->name('force-delete');
        Route::get('/search', 'search')->name('search');
    });
    Route::apiResource('companies', CompanyController::class);

    Route::get('/user-settings', [UserSettingController::class, 'index'])->name('user-settings.index');
    Route::patch('/user-settings', [UserSettingController::class, 'update'])->name('user-settings.update');

    Route::prefix('chatwork')->name('chatwork.')->controller(ChatworkController::class)->group(function () {
        Route::get('/room/{roomId}', 'roomDetail')->name('room.detail');
        Route::get('/room/{roomId}/members', 'roomMembers')->name('room.members');
        Route::post('/send-message/{roomId}', 'sendMessage')->name('send-message');
    });

    Route::prefix('teams')->name('teams.')->controller(TeamController::class)->group(function () {
        Route::get('/company/{company}', 'getListByCompany')->name('get-list-by-company');
        Route::get('/search', 'search')->name('search');
    });
    Route::apiResource('teams', TeamController::class);
});
