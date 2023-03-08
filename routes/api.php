<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::get('login', [AuthController::class,'unAuthenticate'])->name('login');

Route::group([
    'middleware' => 'auth:api',
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    Route::post('user/logout', 'AuthController@logout');
    Route::post('user/refresh', 'AuthController@refresh');
    Route::post('user/me', 'AuthController@me');
    //user controller route
    Route::post('user/user-profile-update',[UserController::class,'userProfileUpdate']);
    Route::get('user/get-tasks-item',[UserController::class,'getTaskItem']);
    Route::get('user/get-all-tasks-item',[UserController::class,'getAllTaskItem']);
    Route::post('user/save-tasks-item',[UserController::class,'saveTaskItem']);
    Route::post('user/update-tasks-item',[UserController::class,'updateTaskItem']);
    Route::post('user/delete-tasks-item',[UserController::class,'deleteTaskItem']);
    Route::post('user/update-tasks-progress',[UserController::class,'updateTaskList']);
});
Route::prefix('user')->group(function($route){
    Route::post('login', [AuthController::class,'login']);
    Route::post('forget-password',[UserController::class,'sendEmail']);
    Route::get('check-user-reset-password-time/{resetcode}',[UserController::class,'checkResetPasswordTime']);
    Route::post('reset-password/{resetcode}',[UserController::class,'resetPassword']);
    Route::post('send-otp-create-account',[UserController::class,'sendOTPCreateAccount']);
    Route::post('verify-otp-create-account',[UserController::class,'verifyOTPCreateAccount']);
    Route::post('set-password/{resetcode}',[UserController::class,'setPassword']);
});
