<?php

namespace App\Http\Controllers;

use anlutro\LaravelSettings\Facade as Setting;
use App;
use App\Models\TrainingInterest;
use App\Models\TrainingReport;
use App\Models\User;
use App\Models\Vote;
use App\Models\Event;
use App\Models\EventRoster;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Controller for the dashboard
 */
class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        $report = TrainingReport::whereIn('training_id', $user->trainings->pluck('id'))->orderBy('created_at')->get()->last();

        $subdivision = $user->subdivision;
        if (empty($subdivision)) {
            $subdivision = 'No subdivision';
        }

        $data = [
            'rating' => $user->rating_long,
            'rating_short' => $user->rating_short,
            'division' => $user->division,
            'subdivision' => $subdivision,
            'report' => $report,
        ];

        $trainings = $user->trainings;
        $statuses = TrainingController::$statuses;
        $types = TrainingController::$types;

        $dueInterestRequest = TrainingInterest::whereIn('training_id', $user->trainings->pluck('id'))->where('expired', false)->get()->first();

        // If the user belongs to our subdivision, doesn't have any training requests, has S2+ rating and is marked as inactive -> show notice
        $allowedSubDivisions = explode(',', Setting::get('trainingSubDivisions'));
        $atcInactiveMessage = (
            (
                (config('app.mode') == 'subdivision' && in_array($user->subdivision, $allowedSubDivisions) && $allowedSubDivisions != null)
                || (config('app.mode') == 'division' && $user->division == config('app.owner_code'))
            )
            && ! $user->hasActiveTrainings(true) && $user->rating > 1 && ! $user->isAtcActive() && ! $user->hasRecentlyCompletedTraining()
        );
        $completedTrainingMessage = $user->hasRecentlyCompletedTraining();

        $workmailRenewal = (isset($user->setting_workmail_expire)) ? (Carbon::parse($user->setting_workmail_expire)->diffInDays(Carbon::now(), false) > -7) : false;

        // Check if there's an active vote running to advertise
        $activeVote = Vote::where('closed', 0)->first();

        $atcHours = ($user->atcActivity->count()) ? $user->atcActivity->sum('hours') : null;

        $studentTrainings = \Auth::user()->mentoringTrainings();

        $cronJobError = (($user->isAdmin() && App::environment('production')) && (\Carbon\Carbon::parse(Setting::get('_lastCronRun', '2000-01-01')) <= \Carbon\Carbon::now()->subMinutes(5)));

        $oudatedVersionWarning = $user->isAdmin() && Setting::get('_updateAvailable');


        //Events
        $events = collect();

        if(Auth::user()->isEventOrAbove()){
            $events = Event::where('end', '>=', Carbon::now())->orderBy('start', 'ASC')->get();
        } else {
            $events = Event::where('end', '>=', Carbon::now())->where('notification_sent', 1)->orderBy('start', 'ASC')->get();
        }

        //Next roster
        /*$nextRoster = EventRoster::where('user_id', Auth::user()->id)->withWhereHas('event',
        function ($q){ 
            $q->where('end', '>=', Carbon::now())->where('roster_published', 1);
        }
        )->with('position')->orderBy('from', 'ASC')->first();*/
        //^ this one is the initial one, it doesn't include the mentors search but i'll leave it here just in case

        $nextRoster = EventRoster::with('mentors')
        ->withWhereHas('event',
            function ($q){ 
                $q->where('end', '>=', Carbon::now())->where('roster_published', 1);
            }
        )
        ->where(function($query){
            $query->whereHas('mentors', function($query){
                $query->where('user_id', Auth::user()->id);
            })
            ->orWhere('user_id', Auth::user()->id);
        })
        ->orderBy('from', 'ASC')->first();

        return view('dashboard', compact('data', 'trainings', 'statuses', 'types', 'dueInterestRequest', 'atcInactiveMessage', 'completedTrainingMessage', 'activeVote', 'atcHours', 'workmailRenewal', 'studentTrainings', 'cronJobError', 'oudatedVersionWarning', 'events', 'nextRoster'));
    }

    /**
     * Show the training apply view
     *
     * @return \Illuminate\View\View
     */
    public function apply()
    {
        return view('trainingapply');
    }

    /**
     * Show member endorsements view
     *
     * @return \Illuminate\View\View
     */
    public function endorsements()
    {
        $members = User::has('ratings')->get()->sortBy('name');

        return view('endorsements', compact('members'));
    }
}
