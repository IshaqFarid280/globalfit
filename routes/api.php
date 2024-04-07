<?php

use App\Http\Controllers\API\ChatMessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'checkSubscription'], function () {
//  categories
    Route::get('/category', [\App\Http\Controllers\API\CategoryController::class, 'index']);
    Route::post('/category/store', [\App\Http\Controllers\API\CategoryController::class, 'store']);
    Route::get('/category/{id}/edit', [\App\Http\Controllers\API\CategoryController::class, 'edit']);
    Route::post('/category/update/{id}', [\App\Http\Controllers\API\CategoryController::class, 'update']);
    Route::get('/category/delete/{id}', [\App\Http\Controllers\API\CategoryController::class, 'destroy']);
    Route::get('/category/{id}', [\App\Http\Controllers\API\CategoryController::class, 'categoryExercises']);

//  exercises
    Route::get('/exercise', [\App\Http\Controllers\API\ExerciseController::class, 'index']);
    Route::post('/exercise', [\App\Http\Controllers\API\ExerciseController::class, 'store']);
    Route::get('/exercise/{id}/edit', [\App\Http\Controllers\API\ExerciseController::class, 'edit']);
    Route::post('/exercise/update/{id}', [\App\Http\Controllers\API\ExerciseController::class, 'update']);
    Route::get('/exercise/delete/{id}', [\App\Http\Controllers\API\ExerciseController::class, 'destroy']);

//    auth
    Route::post('/signUp', [\App\Http\Controllers\API\AuthController::class, 'signUp']);
    Route::post('/login', [\App\Http\Controllers\API\AuthController::class, 'login']);
    Route::post('/logout', [\App\Http\Controllers\API\AuthController::class, 'logout']);
    Route::post('/password/forget', [\App\Http\Controllers\API\AuthController::class, 'forget']);
    Route::post('/verify_code', [\App\Http\Controllers\API\AuthController::class, 'codeVerifiy']);
    Route::post('/reset_password', [\App\Http\Controllers\API\AuthController::class, 'newPassword']);

//    subscription
    Route::post('/subscription', [\App\Http\Controllers\API\SubscriptionController::class, 'store']);
    Route::get('/subscription', [\App\Http\Controllers\API\SubscriptionController::class, 'index']);

//    user information
    Route::post('/user_information', [\App\Http\Controllers\API\UserInfoController::class, 'store']);
    Route::get('/users_information', [\App\Http\Controllers\API\UserInfoController::class, 'index']);
    Route::get('/user_information/{id}/edit', [\App\Http\Controllers\API\UserInfoController::class, 'edit']);
    Route::post('/user_information/update/{id}', [\App\Http\Controllers\API\UserInfoController::class, 'update']);
    Route::get('/user_information/delete/{id}', [\App\Http\Controllers\API\UserInfoController::class, 'destroy']);
    Route::get('/user_information/single_user_info/{id}', [\App\Http\Controllers\API\UserInfoController::class, 'singleUserInfo']);

//    programs
    Route::post('/program', [\App\Http\Controllers\API\ProgramController::class, 'store']);
    Route::get('/program', [\App\Http\Controllers\API\ProgramController::class, 'index']);
    Route::get('/program/{id}/edit', [\App\Http\Controllers\API\ProgramController::class, 'edit']);
    Route::post('/program/{id}', [\App\Http\Controllers\API\ProgramController::class, 'update']);
    Route::get('/program/{id}/destroy', [\App\Http\Controllers\API\ProgramController::class, 'destroy']);
    Route::post('/program/isCompleted/{id}', [\App\Http\Controllers\API\ProgramController::class, 'isCompeted']);
    Route::get('/program_assign_users', [\App\Http\Controllers\API\ProgramController::class, 'programAssignUsers']);
    Route::get('/program_unassign_users', [\App\Http\Controllers\API\ProgramController::class, 'programUnassignUsers']);

//    days
    Route::post('/day', [\App\Http\Controllers\API\DayController::class, 'store']);
    Route::get('/day/{id}', [\App\Http\Controllers\API\DayController::class, 'index']);
    Route::get('/day/{id}/edit', [\App\Http\Controllers\API\DayController::class, 'edit']);
    Route::post('/day/{id}', [\App\Http\Controllers\API\DayController::class, 'update']);
    Route::get('/day/{id}/destroy', [\App\Http\Controllers\API\DayController::class, 'destroy']);
    Route::post('/day/isCompleted/{id}', [\App\Http\Controllers\API\DayController::class, 'isCompeted']);

//    sets
    Route::post('/set', [\App\Http\Controllers\API\SetController::class, 'store']);
    Route::get('/set/{id}', [\App\Http\Controllers\API\SetController::class, 'index']);
    Route::get('/set/{id}/edit', [\App\Http\Controllers\API\SetController::class, 'edit']);
    Route::post('/set/{id}', [\App\Http\Controllers\API\SetController::class, 'update']);
    Route::get('/set/{id}/destroy', [\App\Http\Controllers\API\SetController::class, 'destroy']);
    Route::post('/set/isCompleted/{id}', [\App\Http\Controllers\API\SetController::class, 'isCompeted']);

//    create program
    Route::post('/programs', [\App\Http\Controllers\API\ProgramCreateController::class, 'store']);
    Route::get('/program_workouts', [\App\Http\Controllers\API\ProgramCreateController::class, 'index']);

//    assign program
    Route::post('/program_assign', [\App\Http\Controllers\API\AssignProgramController::class,'store']);
    Route::get('/assigned_program', [\App\Http\Controllers\API\AssignProgramController::class,'index']);
    Route::get('/user_assigned_programs', [\App\Http\Controllers\API\AssignProgramController::class,'userAssignedPrograms']);
    Route::get('/assigned_program/delete/{id}', [\App\Http\Controllers\API\AssignProgramController::class,'delete']);

//    day exercises
    Route::post('/day_exercise', [\App\Http\Controllers\API\DayExerciseController::class,'store']);
    Route::get('/day_exercise/{id}', [\App\Http\Controllers\API\DayExerciseController::class,'index']);
    Route::get('/day_exercise/{id}/edit', [\App\Http\Controllers\API\DayExerciseController::class,'edit']);
    Route::post('/day_exercise/{id}', [\App\Http\Controllers\API\DayExerciseController::class,'update']);
    Route::get('/day_exercise/{id}/destroy', [\App\Http\Controllers\API\DayExerciseController::class,'destroy']);
    Route::post('/day_exercise/isCompleted/{id}', [\App\Http\Controllers\API\DayExerciseController::class,'isCompeted']);

//    users
    Route::get('/users', [\App\Http\Controllers\API\UserController::class,'index']);

//    device token
    Route::post('/device_token', [\App\Http\Controllers\API\DeviceTokenController::class, 'store']);
    Route::get('/device_token', [\App\Http\Controllers\API\DeviceTokenController::class, 'index']);

//    notifications
    Route::get('/notification', [\App\Http\Controllers\API\NotificationController::class,'index']);

//    chating
    Route::resource('chat-messages', ChatMessageController::class)->except(['create', 'edit']);
});
