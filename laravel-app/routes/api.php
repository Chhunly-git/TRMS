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
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\DocumentTemplateController;
use App\Http\Controllers\API\WorkScheduleController;
use App\Http\Controllers\API\MeetingRoomController;
use App\Http\Controllers\API\RoomBookingController;

use Illuminate\Support\Facades\Route;


Route::post('/signin', [AuthController::class, 'signin']);
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
Route::get('/manage/get-dashboard-summary', [AttendanceController::class, 'getDashboardSummary']);

Route::middleware(['auth:sanctum', 'enabled'])->group(function () {
    Route::post('/signout', [AuthController::class, 'signout']);
    Route::put('/create/password', [AuthController::class, 'createPassword']);
    Route::put('/change/password', [AuthController::class, 'changePassword']);
    Route::put('/update/profile-image', [AuthController::class, 'updateProfileImage']);
    Route::delete('/delete/profile-image', [AuthController::class, 'deleteProfileImage']);
    Route::get('/manage/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/my-attendances', [AttendanceController::class, 'myAttendances']);
    Route::get('/manage/profile', [AuthController::class, 'getProfile']);
    Route::get('/my-profile', [AuthController::class, 'getProfile']);
    Route::get('/document-templates', [DocumentTemplateController::class, 'index']);
    
    // កាលវិភាគការងារ និងកិច្ចប្រជុំ (Work Schedules & Meetings)
    Route::prefix('work-schedules')->group(function () {
        Route::get('/', [WorkScheduleController::class, 'index']);
        Route::get('/summary', [WorkScheduleController::class, 'summary']);
        Route::get('/{id}', [WorkScheduleController::class, 'show']);
        Route::post('/', [WorkScheduleController::class, 'store']);
        Route::put('/{id}', [WorkScheduleController::class, 'update']);
        Route::patch('/{id}/status', [WorkScheduleController::class, 'updateStatus']);
        Route::delete('/{id}', [WorkScheduleController::class, 'destroy']);
    });

    // បន្ទប់ប្រជុំ (Meeting Rooms)
    Route::prefix('meeting-rooms')->group(function () {
        Route::get('/', [MeetingRoomController::class, 'index']);
        Route::get('/availability', [MeetingRoomController::class, 'checkAvailability']);
        Route::get('/{id}', [MeetingRoomController::class, 'show']);
        Route::post('/', [MeetingRoomController::class, 'store']);
        Route::match(['PUT', 'POST'], '/{id}', [MeetingRoomController::class, 'update']);
        Route::delete('/{id}', [MeetingRoomController::class, 'destroy']);
    });

    // ការកក់បន្ទប់ប្រជុំ (Room Bookings)
    Route::prefix('room-bookings')->group(function () {
        Route::get('/', [RoomBookingController::class, 'index']);
        Route::get('/timetable', [RoomBookingController::class, 'timetable']);
        Route::get('/my-bookings', [RoomBookingController::class, 'myBookings']);
        Route::get('/{id}', [RoomBookingController::class, 'show']);
        Route::post('/', [RoomBookingController::class, 'store']);
        Route::match(['PUT', 'POST'], '/{id}', [RoomBookingController::class, 'update']);
        Route::match(['POST', 'PATCH'], '/{id}/cancel', [RoomBookingController::class, 'cancel']);
        Route::match(['POST', 'PATCH'], '/{id}/approve', [RoomBookingController::class, 'approve']);
        Route::match(['POST', 'PATCH'], '/{id}/reject', [RoomBookingController::class, 'reject']);
        Route::delete('/{id}', [RoomBookingController::class, 'destroy']);
    });
    
    Route::middleware('admin')->prefix('manage')->group(function () {
        
        // 1. Users & Employees Routes (Unified under UserController)
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'getUsers']);
            Route::get('/read/{id}', [UserController::class, 'readUser']);
            Route::post('/create', [UserController::class, 'createUser']);
            Route::match(['PUT', 'POST'], '/update/{id}', [UserController::class, 'updateUser']);
            Route::put('/permissions/{id}', [UserController::class, 'updateUserPermissions']);
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


        // 5. Attendances (គ្រប់គ្រងវត្តមាន)
        // routeName សម្រាប់ទាញយកបញ្ជីវត្តមានប្រចាំថ្ងៃ (អាចផ្ញើ query date មកជាមួយបាន)
        Route::get('/attendances', [AttendanceController::class, 'index']);
        
        // routeName សម្រាប់រក្សាទុក ឬកែប្រែវត្តមានមន្ត្រី
        Route::post('/attendances/save', [AttendanceController::class, 'store']);
        Route::post('/attendances/import', [AttendanceController::class, 'import']);

        // 6. Document Templates (គ្រប់គ្រងគំរូឯកសារ)
        Route::prefix('document-templates')->group(function () {
            Route::get('/', [DocumentTemplateController::class, 'index']);
            Route::post('/create', [DocumentTemplateController::class, 'store']);
            Route::post('/update/{id}', [DocumentTemplateController::class, 'update']);
            Route::delete('/delete/{id}', [DocumentTemplateController::class, 'destroy']);
        });
    });
});