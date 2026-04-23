<?php

namespace App\Http\Controllers;

use anlutro\LaravelSettings\Facade as Setting;
use App\Models\Feedback;
use App\Models\Position;
use App\Models\User;
use App\Notifications\FeedbackNotification;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        if (! Setting::get('feedbackEnabled')) {
            return redirect()->route('dashboard')->withErrors('Feedback is currently disabled.');
        }

        $this->authorize('create', Feedback::class);

        $positions = Position::all();
        $controllers = User::getActiveAtcMembers();

        return view('feedback.create', compact('positions', 'controllers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (! Setting::get('feedbackEnabled')) {
            return redirect()->route('dashboard')->withErrors('Feedback is currently disabled.');
        }

        $this->authorize('create', Feedback::class);

        $data = $request->validate([
            'position' => 'nullable|exists:positions,callsign',
            'controller' => 'nullable|exists:users,id',
            'time' => 'nullable|string',
            'feedback' => 'required',
        ]);

        $position = isset($data['position']) ? Position::where('callsign', $data['position'])->get()->first() : null;
        $controller = isset($data['controller']) ? User::find($data['controller']) : null;
        $feedback = $data['feedback'];
        $time = $data['time'];

        $submitter = auth()->user();

        if($submitter->is($controller)){
            return redirect()->back()->withErrors(["You can not write feedback for yourself"]);
        }

        $feedback = Feedback::create([
            'feedback' => $feedback,
            'submitter_user_id' => $submitter->id,
            'reference_user_id' => isset($controller) ? $controller->id : null,
            'reference_position_id' => isset($position) ? $position->id : null,
            'time' => $time,
        ]);

        // Forward email if configured
        if (Setting::get('feedbackForwardEmail')) {
            $feedback->notify(new FeedbackNotification($feedback));
        }

        return redirect()->route('dashboard')->with('success', 'Feedback submitted!');

    }

    public function show($id = null){

        if($id){
            $user = User::find($id);

            $this->authorize('view', [Feedback::class, $user]);
        } else {
            $this->authorize('viewAll', Feedback::class);
        }

        $feedback = Feedback::orderBy('created_at', 'DESC');

        if($id){
            $feedback = $feedback->where('reference_user_id', $id);
        }

        $feedback = $feedback->paginate(15);

        $feedbackUser = User::find($id);

        return view('feedback.show', compact('feedback', 'feedbackUser'));

    }

    public function acknowledge(Request $r){

        $this->authorize('acknowledge', Feedback::class);

        $data = $r->validate([
            'feedback_id' => 'required|exists:feedback,id'
        ]);

        $id = $data['feedback_id'];

        $feedback = Feedback::find($id);

        $feedback->acknowledged = true;

        $feedback->save();

        return response()->json(['success' => 'success'], 200);
    }
}
