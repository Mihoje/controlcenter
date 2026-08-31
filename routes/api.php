<?php

use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\PositionController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\EventsController;
use App\Http\Controllers\API\FileMainController;
use App\Http\Controllers\API\StaffMemberController;
use App\Http\Controllers\API\TestAPIController;
use App\Http\Controllers\API\StatsController;
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
    Route::post('/bookings/create', [BookingController::class, 'store'])->name('api.booking.store');
    Route::patch('/bookings/{booking}', [BookingController::class, 'update'])->name('api.booking.update');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('api.booking.destroy');
});

Route::group(['middleware' => ['api-token:euroscope']], function () {
    Route::post('/estest', [TestAPIController::class, 'testFunction'])->name('api.test');
});
Route::get('/stats/all', [StatsController::class, 'allStats'])->name('api.stats.allstats');

Route::group(['middleware' => ['api-token']], function () {
    Route::get('/bookings', [BookingController::class, 'index'])->name('api.booking.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('api.booking.show');
    Route::get('/positions', [PositionController::class, 'index'])->name('api.positions.index');

    Route::get('/users', [UserController::class, 'index'])->name('api.users.index');


    //new
    Route::get('/events/upcoming', [EventsController::class, 'upcomingEvents'])->name('api.events.upcoming');
    Route::get('/events/in_hours/{hours}', [EventsController::class, 'soonEvent'])->name('api.events.soon');
    Route::get('/events/future', [EventsController::class, 'futureEvents'])->name('api.events.future');
    Route::get('/events/{id}/getdetails', [EventsController::class, 'getEventDetails'])->name('api.events.getdetails');

    Route::get('/userdata/{id}', [UserController::class, 'getUserData'])->name('api.users.getdata');

    Route::get('/file_main/get', [FileMainController::class, 'getFiles'])->name('api.filemain.get');

    Route::get('/staff_members/get', [StaffMemberController::class, 'get'])->name('api.staffmembers.get');
});
