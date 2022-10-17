<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\User\ReferralController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\User\UserProfileSettingsController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//real user application Apis
Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1'], function () {
    Route::post('auth/register', [RegisterController::class, 'create']);
    Route::post('auth/verify_email', [RegisterController::class, 'verifyEmail']);
    Route::post('auth/resend_email_verification', [RegisterController::class, 'resendEmailVerification']);
    Route::post('auth/login', [LoginController::class, 'login']);
    Route::post('auth/reset_password', [ResetPasswordController::class, 'reset_password'])->middleware('auth:sanctum');

    /*
    Route::post('auth/logout', 'Auth\LoginController@logout')->middleware('auth:api');
    */

    

    //Reward System
    Route::get('reward/referral_detail', [ReferralController::class, 'referralDetail'])->middleware('auth:sanctum');

    // Profile Settings
    Route::get('profile/get_my_profile', [UserProfileSettingsController::class, 'getMyProfile'])->middleware('auth:sanctum');
    Route::post('profile/update_my_profile', [UserProfileSettingsController::class, 'updateMyProfile'])->middleware('auth:sanctum');

    
    Route::fallback(function () {
        return response()->json(['message' => 'Page Not Found'], 404);
    });
});



