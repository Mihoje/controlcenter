<?php

use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\PositionController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\EventsController;
use App\Http\Controllers\API\FileMainController;
use App\Http\Controllers\API\StaffMemberController;
use App\Http\Controllers\API\TestAPIController;
use App\Http\Controllers\API\StatsController;

/*
| API v1 routes — mirrors routes/api.php under the api/v1 prefix.
| Points at the same controllers; names are prefixed api.v1.*.
*/

Route::middleware('auth:api')->get('/user', [UserController::class, 'authenticated'])->name('api.v1.user');

Route::group(['middleware' => ['api-token:edit']], function () {
    Route::post('/bookings/create', [BookingController::class, 'store'])->name('api.v1.booking.store');
    Route::patch('/bookings/{booking}', [BookingController::class, 'update'])->name('api.v1.booking.update');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('api.v1.booking.destroy');
});

Route::group(['middleware' => ['api-token:euroscope']], function () {
    Route::post('/estest', [TestAPIController::class, 'testFunction'])->name('api.v1.test');
});
Route::get('/stats/all', [StatsController::class, 'allStats'])->name('api.v1.stats.allstats');

Route::group(['middleware' => ['api-token:edit']], function () {
    Route::post('/bookings/create', [BookingController::class, 'store'])->name('api.v1.booking.store');
    Route::patch('/bookings/{booking}', [BookingController::class, 'update'])->name('api.v1.booking.update');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('api.v1.booking.destroy');
});

Route::group(['middleware' => ['api-token']], function () {
    Route::get('/bookings', [BookingController::class, 'index'])->name('api.v1.booking.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('api.v1.booking.show');
    Route::get('/positions', [PositionController::class, 'index'])->name('api.v1.positions.index');
    Route::get('/users', [UserController::class, 'index'])->name('api.v1.users.index');

    //new
    Route::get('/events/upcoming', [EventsController::class, 'upcomingEvents'])->name('api.v1.events.upcoming');
    Route::get('/events/in_hours/{hours}', [EventsController::class, 'soonEvent'])->name('api.v1.events.soon');
    Route::get('/events/future', [EventsController::class, 'futureEvents'])->name('api.v1.events.future');
    Route::get('/events/{id}/getdetails', [EventsController::class, 'getEventDetails'])->name('api.v1.events.getdetails');

    Route::get('/userdata/{id}', [UserController::class, 'getUserData'])->name('api.v1.users.getdata');

    Route::get('/file_main/get', [FileMainController::class, 'getFiles'])->name('api.v1.filemain.get');

    Route::get('/staff_members/get', [StaffMemberController::class, 'get'])->name('api.v1.staffmembers.get');
});
