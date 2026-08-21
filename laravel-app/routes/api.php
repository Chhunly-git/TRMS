<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BackupController;
use App\Http\Controllers\API\GoogleOAuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\GeoController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\OfficeController;
use App\Http\Controllers\API\PositionController;
use App\Http\Controllers\API\DashboardController;
use Illuminate\Support\Facades\Route;

// Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/signin', [AuthController::class, 'signin']);
// Route::get('/verify/email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
//     ->middleware('signed')
//     ->name('verify.email');
// Route::post('/send/verification-email', [AuthController::class, 'sendVerificationEmail']);
Route::post('/send/reset-password-email', [AuthController::class, 'sendResetPasswordEmail']);
Route::post('/set/new-password', [AuthController::class, 'setNewPassword'])->name('set.new-password');

Route::prefix('google')->group(function () {
    Route::get('/oauth/redirect', [GoogleOAuthController::class, 'googleOAuthRedirect']);
    Route::get('/oauth/callback', [GoogleOAuthController::class, 'googleOAuthCallback']);
    Route::post('/oauth/exchange/token', [GoogleOAuthController::class, 'googleOAuthExchangeToken'])->middleware('auth:sanctum');
});

Route::get('/provinces', [GeoController::class, 'getProvinces']);
Route::get('/districts/by/province/{id}', [GeoController::class, 'getDistrictsByProvince']);
Route::get('/communes/by/district/{id}', [GeoController::class, 'getCommunesByDistrict']);
Route::get('/villages/by/commune/{id}', [GeoController::class, 'getVillagesByCommune']);

Route::middleware(['auth:sanctum', 'enabled'])->group(function () {
    Route::post('/signout', [AuthController::class, 'signout']);
    // Route::get('/verify', [AuthController::class, 'verify']);
    Route::put('/create/password', [AuthController::class, 'createPassword']);
    Route::put('/change/password', [AuthController::class, 'changePassword']);
    Route::put('/update/profile-image', [AuthController::class, 'updateProfileImage']);
    Route::delete('/delete/profile-image', [AuthController::class, 'deleteProfileImage']);
    Route::get('/manage/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/manage/profile', [AuthController::class, 'getProfile']);
    Route::middleware('admin')->prefix('manage')->group(function () {
        
        // 1. Users & Employees Routes (Unified under UserController)
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'getUsers']);
            Route::get('/read/{id}', [UserController::class, 'readUser']);
            Route::post('/create', [UserController::class, 'createUser']);
            Route::put('/update/{id}', [UserController::class, 'updateUser']);
            Route::patch('/toggle-status/{id}', [UserController::class, 'toggleUserStatus']);
            Route::delete('/delete/{id}', [UserController::class, 'deleteUser']);
        });


        // Backups Routes
        Route::prefix('backups')->group(function () {
            Route::get('/', [BackupController::class, 'getBackups']);
            Route::post('/create', [BackupController::class, 'createBackup']);
            Route::get('/download/{filename}', [BackupController::class, 'downloadBackup']);
            Route::delete('/delete/{filename}', [BackupController::class, 'deleteBackup']);
        });

        // 2. Departments (គ្រប់គ្រងនាយកដ្ឋាន)
        Route::prefix('departments')->group(function () {
            Route::get('/', [DepartmentController::class, 'getDepartments']);
            Route::get('/read/{id}', [DepartmentController::class, 'readDepartment']);
            Route::post('/create', [DepartmentController::class, 'createDepartment']);
            Route::put('/update/{id}', [DepartmentController::class, 'updateDepartment']);
            Route::delete('/delete/{id}', [DepartmentController::class, 'deleteDepartment']);
        });

        // 3. Offices (គ្រប់គ្រងការិយាល័យ)
        Route::prefix('offices')->group(function () {
            Route::get('/', [OfficeController::class, 'getOffices']);
            Route::get('/by-department/{department_id}', [OfficeController::class, 'getOfficesByDepartment']);
            Route::post('/create', [OfficeController::class, 'createOffice']);
            Route::put('/update/{id}', [OfficeController::class, 'updateOffice']);
            Route::delete('/delete/{id}', [OfficeController::class, 'deleteOffice']);
        });

        // 4. Positions (គ្រប់គ្រងតួនាទី)
        Route::prefix('positions')->group(function () {
            Route::get('/', [PositionController::class, 'getPositions']);
            Route::post('/create', [PositionController::class, 'createPosition']);
            Route::get('/read/{id}', [PositionController::class, 'readPosition']);
            Route::put('/update/{id}', [PositionController::class, 'updatePosition']);
            Route::delete('/delete/{id}', [PositionController::class, 'deletePosition']);
        });
    });
});