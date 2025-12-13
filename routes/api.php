<?php

use Illuminate\Http\Request;

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


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['api-token:edit']], function () {
    Route::post('/bookings/create', [App\Http\Controllers\API\BookingController::class, 'store'])->name('api.booking.store');
    Route::patch('/bookings/{booking}', [App\Http\Controllers\API\BookingController::class, 'update'])->name('api.booking.update');
    Route::delete('/bookings/{booking}', [App\Http\Controllers\API\BookingController::class, 'destroy'])->name('api.booking.destroy');
});

Route::group(['middleware' => ['api-token:euroscope']], function () {
    Route::post('/estest', [App\Http\Controllers\API\TestAPIController::class, 'testFunction'])->name('api.test');
});
Route::get('/stats/all', [App\Http\Controllers\API\StatsController::class, 'allStats'])->name('api.stats.allstats');

Route::group(['middleware' => ['api-token']], function () {
    Route::get('/bookings', [App\Http\Controllers\API\BookingController::class, 'index'])->name('api.booking.index');
    Route::get('/bookings/{booking}', [App\Http\Controllers\API\BookingController::class, 'show'])->name('api.booking.show');

    Route::get('/positions', [App\Http\Controllers\API\PositionController::class, 'index'])->name('api.positions.index');

    Route::get('/users', [App\Http\Controllers\API\UserController::class, 'index'])->name('api.users.index');


    //new
    Route::get('/events/upcoming', [App\Http\Controllers\API\EventsController::class, 'upcomingEvents'])->name('api.events.upcoming');
    Route::get('/events/in_hours/{hours}', [App\Http\Controllers\API\EventsController::class, 'soonEvent'])->name('api.events.soon');
    Route::get('/events/future', [App\Http\Controllers\API\EventsController::class, 'futureEvents'])->name('api.events.future');
    Route::get('/events/{id}/getdetails', [App\Http\Controllers\API\EventsController::class, 'getEventDetails'])->name('api.events.getdetails');

    Route::get('/userdata/{id}', [App\Http\Controllers\API\UserController::class, 'getUserData'])->name('api.users.getdata');

    Route::get('/file_main/get', [App\Http\Controllers\API\FileMainController::class, 'getFiles'])->name('api.filemain.get');

    Route::get('/staff_members/get', [App\Http\Controllers\API\StaffMemberController::class, 'get'])->name('api.staffmembers.get');
});