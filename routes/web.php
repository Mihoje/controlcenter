<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EndorsementController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FrontPageController;
use App\Http\Controllers\GlobalSettingController;
use App\Http\Controllers\MentorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OneTimeLinkController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\SweatbookController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TrainingActivityController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TrainingExaminationController;
use App\Http\Controllers\TrainingObjectAttachmentController;
use App\Http\Controllers\TrainingReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\FileMainController;
use App\Http\Controllers\BlockedController;
use App\Http\Controllers\AirportEndorsementController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// --------------------------------------------------------------------------
// Main page
// --------------------------------------------------------------------------
Route::get('/', [FrontPageController::class, 'index'])->name('front');

// --------------------------------------------------------------------------
// VATSIM Authentication
// --------------------------------------------------------------------------
Route::get('/login', [LoginController::class, 'login'])->middleware('guest')->name('login');
Route::get('/validate', [LoginController::class, 'validateLogin'])->middleware('guest');
Route::get('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// --------------------------------------------------------------------------
// Sites behind authentication
// --------------------------------------------------------------------------
Route::middleware(['auth', 'activity'])->group(function () {
    // Sidebar Navigation
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/content', [DashboardController::class, 'content'])->name('content');
    Route::get('/mentor', [DashboardController::class, 'mentor'])->name('mentor');
    Route::get('/trainings', [TrainingController::class, 'index'])->name('requests');
    Route::get('/trainings/history', [TrainingController::class, 'history'])->name('requests.history');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/users/other', [UserController::class, 'indexOther'])->name('users.other');

    // Endorsements
    Route::controller(EndorsementController::class)->group(function () {
        Route::get('/endorsements/solos', 'indexSolos')->name('endorsements.solos');
        Route::get('/endorsements/examiners', 'indexExaminers')->name('endorsements.examiners');
        Route::get('/endorsements/visiting', 'indexVisitors')->name('endorsements.visiting');
        Route::get('/endorsements/create', 'create')->name('endorsements.create');
        Route::get('/endorsements/create/{id}', 'create')->name('endorsements.create.id');
        Route::post('/endorsements/store', 'store')->name('endorsements.store');
        Route::get('/endorsements/{id}/delete', 'destroy')->name('endorsements.delete');
        Route::get('/endorsements/shorten/{id}/{date}', 'shorten')->name('endorsements.shorten');
    });

    // ATC Roster
    Route::get('/roster/{area}', [RosterController::class, 'index'])->name('roster');

    // Users
    Route::controller(UserController::class)->group(function () {
        Route::get('/user/{user}', 'show')->name('user.show');
        Route::get('/user/{user}/reports', 'reports')->name('user.reports');
        Route::get('/settings', 'settings')->name('user.settings');
        Route::post('/settings', 'settings_update')->name('user.settings.store');
        Route::post('/settings/theme', 'settings_update_theme')->name('user.settings.theme');
        Route::get('/settings/extendworkmail', 'extendWorkmail')->name('user.settings.extendworkmail');

        // Internal user search
        Route::get('/user/search/find', 'search')->name('user.search');
        Route::get('/user/search/vatsimhours', 'fetchVatsimHours')->name('user.vatsimhours');
        Route::get('/user/{user}/statistics/sessions', 'fetchStatisticsSessions')->name('user.statistics.sessions');
    });

    // Reports
    Route::controller(ReportController::class)->group(function () {
        Route::get('/reports/trainings', 'trainings')->name('reports.trainings');
        Route::get('/reports/training/{id}', 'trainings')->name('reports.training.area');
        Route::get('/reports/activities', 'activities')->name('reports.activities');
        Route::get('/reports/activities/{id}', 'activities')->name('reports.activities.area');
        Route::get('/reports/mentors', 'mentors')->name('reports.mentors');
        Route::get('/reports/access', 'access')->name('reports.access');
        Route::get('/reports/feedback', 'feedback')->name('reports.feedback');

        Route::get('/reports/members', 'members')->name('reports.members');
    });

    // Admin
    Route::get('/admin/settings', [GlobalSettingController::class, 'index'])->name('admin.settings');
    Route::post('/admin/settings', [GlobalSettingController::class, 'edit'])->name('admin.settings.store');
    Route::get('/admin/templates', [NotificationController::class, 'index'])->name('admin.templates');
    Route::get('/admin/templates/{id}', [NotificationController::class, 'index'])->name('admin.templates.area');
    Route::post('/admin/templates/update', [NotificationController::class, 'update'])->name('admin.templates.update');
    Route::get('/admin/log', [ActivityLogController::class, 'index'])->name('admin.logs');
    Route::resource('/admin/positions', PositionController::class)->except(['show']);
    Route::get('/admin/positions/{area}', [PositionController::class, 'index'])->name('positions.index.area');

    // Training routes
    Route::controller(TrainingController::class)->group(function () {
        Route::get('/training/apply', 'apply')->name('training.apply');
        Route::get('/training/create', 'create')->name('training.create');
        Route::get('/training/create/{id}', 'create')->name('training.create.id');
        Route::get('/training/edit/{training}', 'edit')->name('training.edit');
        Route::patch('/training/edit/{training}', 'updateRequest')->name('training.update.request');
        Route::post('/training/store', 'store')->name('training.store');
        Route::get('/training/{training}/action/close', 'close')->name('training.action.close');
        Route::get('/training/{training}/action/pretraining', 'togglePreTrainingCompleted')->name('training.action.pretraining');
        Route::patch('/training/{training}', 'updateDetails')->name('training.update.details');
        Route::get('/training/{training}', 'show')->name('training.show');

        Route::get('/training/{training}/confirm/{key}', 'confirmInterest')->name('training.confirm.interest');
    });

    // Training report routes
    Route::controller(TrainingReportController::class)->group(function () {
        Route::get('/training/report/{report}', 'edit')->name('training.report.edit');
        Route::get('/training/{training}/report/create', 'create')->name('training.report.create');
        Route::post('/training/{training}/report', 'store')->name('training.report.store');
        Route::patch('/training/report/{report}', 'update')->name('training.report.update');
        Route::get('/training/report/{report}/delete', 'destroy')->name('training.report.delete');
    });

    // Training object routes
    Route::controller(OneTimeLinkController::class)->group(function () {
        Route::get('/training/onetime/{key}', 'redirect')->name('training.onetimelink.redirect');
        Route::post('/training/{training}/onetime', 'store')->name('training.onetimelink.store');
    });

    // Training object attachment routes
    Route::controller(TrainingObjectAttachmentController::class)->group(function () {
        Route::get('/training/attachment/{attachment}', 'show')->name('training.object.attachment.show');
        Route::post('/training/{trainingObjectType}/{trainingObject}/attachment', 'store')->name('training.object.attachment.store');
        Route::delete('/training/attachment/{attachment}', 'destroy')->name('training.object.attachment.delete');
    });

    // Training examination routes
    Route::controller(TrainingExaminationController::class)->group(function () {
        Route::get('/training/examination/{examination}', 'show')->name('training.examination.show');
        Route::get('/training/{training}/examination/create', 'create')->name('training.examination.create');
        Route::post('/training/{training}/examination', 'store')->name('training.examination.store');
        Route::patch('/training/examination/{examination}', 'update')->name('training.examination.update');
        Route::get('/training/examination/{examination}/delete', 'destroy')->name('training.examination.delete');
    });

    Route::post('/training/activity/comment', [TrainingActivityController::class, 'storeComment'])->name('training.activity.comment');

    Route::controller(FileController::class)->group(function () {
        Route::get('/files/{file}', 'get')->name('file.get');
        Route::post('/files', 'store')->name('file.store');
        Route::delete('/files/{file}', 'destroy')->name('file.delete');
    });

    // Sweatbook routes
    Route::controller(SweatbookController::class)->group(function () {
        Route::get('/sweatbook', 'index')->name('sweatbook');
        Route::get('/sweatbook/{id}/delete', 'delete')->name('sweatbook.delete');
        Route::get('/sweatbook/{id}', 'show');
        Route::post('/sweatbook/store', 'store');
        Route::post('/sweatbook/update', 'update');
    });

    // Booking routes
    Route::controller(BookingController::class)->group(function () {
        Route::get('/booking', 'index')->name('booking');
        Route::get('/booking/bulk', 'bulk')->name('booking.bulk');
        Route::post('/booking/bulk', 'storeBulk')->name('booking.bulk.store');
        Route::get('/booking/{id}/delete', 'delete')->name('booking.delete');
        Route::get('/booking/{id}', 'show');
        Route::post('/booking/store', 'store')->name('booking.store');
        Route::post('/booking/update', 'update');
    });

    // Mentor Routes
    Route::get('/mentor', [MentorController::class, 'index'])->name('mentor');

    // Vote routes
    Route::controller(VoteController::class)->group(function () {
        Route::get('/votes', 'index')->name('vote.overview');
        Route::get('/vote/create', 'create')->name('vote.create');
        Route::post('/vote/store', 'store')->name('vote.store');
        Route::patch('/vote/{id}', 'update')->name('vote.update');
        Route::get('/vote/{id}', 'show')->name('vote.show');
    });

    Route::controller(TaskController::class)->group(function () {
        Route::get('/tasks', 'index')->name('tasks');
        Route::get('/tasks/{activeFilter}', 'index')->name('tasks.filtered');
        Route::get('/tasks/complete/{id}', 'complete')->name('task.complete');
        Route::get('/tasks/decline/{id}', 'decline')->name('task.decline');
        Route::post('/task/store', 'store')->name('task.store');
    });

    // Events routes
    Route::controller(EventController::class)->group(function(){
        Route::get('/event/create', 'create')->name('event.create');
        Route::post('/event/store', 'store')->name('event.store');
        Route::get('/event/delete/{id}', 'delete')->name('event.delete');
        Route::get('/event/publish/{id}', 'publish')->name('event.publish');
        Route::get('/event/edit/{id}', 'edit')->name('event.edit');
        Route::patch('/event/patch/{id}', 'patch')->name('event.patch');

        //API
        Route::get('/event/data/{id}', 'showdata')->name('event.api.data');
    });

    //Availabilites
    Route::controller(EventAvailabilityController::class)->group(function(){
        Route::get('/event/{id}/availability/create', 'create')->name('event.avl.create');
        Route::post('/event/{id}/availability/store', 'store')->name('event.avl.store');
        Route::get('/event/{id}/availability/edit', 'edit')->name('event.avl.edit');
        Route::patch('/event/{id}/availability/update', 'update')->name('event.avl.update');
    });

    //Roster
    Route::controller(EventRosterController::class)->group(function(){
        Route::get('/event/{id}/roster/show', 'show')->name('event.roster.create');
        Route::get('/event/{id}/roster/book', 'bookPositions')->name('event.roster.book');

        Route::post('/event/{id}/roster/edit', 'save')->name('event.roster.edit');
        Route::get('/event/{id}/roster/publish', 'publish')->name('event.roster.publish');

        /*Route::post('/event/roster/save', 'save')->name('event.roster.save');
        Route::post('/event/roster/edit/save', 'update')->name('event.roster.edit.save');       */
    });

    //Feedback
    Route::controller(FeedbackController::class)->group(function(){
        Route::get('/feedback/create', 'create')->name('feedback');
        Route::get('/feedback/list', 'show')->name('feedback.list');
        Route::get('/feedback/list/{id}', 'show')->name('feedback.list.user');
        Route::get('/feedback/show', 'show')->name('feedback.show');
        Route::post('/feedback/store', 'store')->name('feedback.store');
        Route::post('/feedback/ack', 'acknowledge')->name('feedback.acknowledge');
        Route::post('/feedback/pub', 'publish')->name('feedback.publish');
        Route::patch('/feedback/{feedback}', 'update')->name('feedback.update');
    });

    //Files main
    Route::get('/filesMain/show', [FileMainController::class, 'show'])->name('filesMain.show');
    Route::get('/filesMain/create', [FileMainController::class, 'create'])->name('filesMain.create');
    Route::post('/filesMain/store', [FileMainController::class, 'store'])->name('filesMain.store');
    Route::get('/filesMain/delete/{id}', [FileMainController::class, 'destroy'])->name('filesMain.delete');

    //Blocked
    Route::controller(BlockedController::class)->group(function(){
        Route::get('/blocked/show', 'show')->name('blocked.show');
        Route::get('/blocked/create', 'create')->name('blocked.create');
        Route::post('/blocked/store', 'store')->name('blocked.store');
        Route::get('/blocked/delete/{id}', 'delete')->name('blocked.delete');
    });

    Route::controller(TrainingBanController::class)->group(function(){
        Route::get('/training_bans', 'show')->name('trainingban.show')->defaults('inactive', false);
        Route::get('/training_bans/inactive', 'show')->name('trainingban.show.inactive')->defaults('inactive', true);
        Route::get('/training_bans/create/{prefillUser?}', 'create')->name('trainingban.create');
        Route::get('/training_bans/{id}/revoke', 'revoke')->name('trainingban.revoke');
        Route::post('/training_bans/store', 'store')->name('trainingban.store');
    });

    Route::controller(StaffMemberController::class)->group(function(){
        Route::get('/staff_members', 'show')->name('staffmembers.show');
        Route::post('/staff_members/store', 'store')->name('staffmembers.store');
        Route::post('/staff_members/order', 'order')->name('staffmembers.order');
        Route::get('/staff_members/destroy/{id}', 'destroy')->name('staffmembers.destroy');
    });

});
