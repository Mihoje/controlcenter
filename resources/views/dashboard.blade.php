@extends('layouts.app')

@section('title', 'Dashboard')
@section('content')

{{-- Success message fed via JS for TR  --}}
<div class="alert alert-success d-none" id="success-message"></div>

@if($dueInterestRequest)
<div class="alert alert-warning" role="alert">
    <i class="fas fa-exclamation-triangle"></i>&nbsp;&nbsp;Please confirm your continued training interest by <a href="{{ route('training.confirm.interest', ['training' => $dueInterestRequest->training->id, 'key' => $dueInterestRequest->key] ) }}">clicking here</a>, within the deadline at {{ $dueInterestRequest->deadline->toEuropeanDate() }}. Your training will be otherwise be closed.
</div>
@endif

@if($atcInactiveMessage)
<div class="alert alert-warning" role="alert">
    <i class="fas fa-exclamation-triangle"></i>&nbsp;&nbsp;Your ATC rating is marked as inactive in this {{ config('app.mode') }}. <a href="{{ Setting::get('linkContact') }}" target="_blank">Contact {{ Setting::get('atcActivityContact') }}</a> to request a refresh or transfer training to be allowed to control in our airspace.
</div>
@endif

@if($completedTrainingMessage)
<div class="alert alert-success" role="alert">
    <i class="fas fa-star"></i>&nbsp;<b>Congratulations on your completed training!</b>&nbsp;<i class="fas fa-star"></i> You'll receive an email from VATSIM when your rating has been upgraded and ready to be used.
</div>
@endif

@if($workmailRenewal)
<div class="alert alert-warning" role="alert">
    <i class="fas fa-exclamation-triangle"></i>&nbsp;&nbsp;Your registered work e-mail address expires soon. <a href="{{ route('user.settings.extendworkmail') }}">Click here to extend for another 30 days</a>. If not extended, all e-mails will go to your default VATSIM account e-mail upon expire.
</div>
@endif

@if($activeVote)
<div class="alert alert-info" role="alert">
    <i class="fas fa-vote-yea"></i>&nbsp;&nbsp;Vote <i>"{{ $activeVote->question }}"</i> is available. Vote closes {{ \Carbon\Carbon::create($activeVote->end_at)->toEuropeanDateTime() }}. <a href="{{ route('vote.show', $activeVote) }}">Click here to vote</a>.
</div>
@endif

@if($cronJobError)
<div class="alert alert-danger" role="alert">
    <i class="fas fa-exclamation-triangle"></i>&nbsp;&nbsp;<b>Configuration Error:</b> Cronjob is not running! Are the cron jobs set up according to the manual?
</div>
@endif

@if($oudatedVersionWarning)
<div class="alert alert-info" role="alert">
    <i class="fas fa-info-circle"></i>&nbsp;&nbsp;<b>Update Available:</b> Control Center {{ Setting::get('_updateAvailable') }} is available. You are running v{{ config('app.version') }}. <a href="https://github.com/Vatsim-Scandinavia/controlcenter/releases" target="_blank">See details here.</a>
</div>
@endif

<div class="row">
    <!-- Current rating card  -->
    <div class="col-xl col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col me-2">
                        <div class="fs-sm fw-bold text-uppercase text-gray-600 mb-1">Current Rating</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">{{ $data['rating'] }} ({{ $data['rating_short'] }})</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-id-badge fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Division card -->
    <div class="col-xl col-md-6 mb-4 d-none d-xl-block d-lg-block d-md-block">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col me-2">
                        <div class="fs-sm fw-bold text-uppercase text-gray-600 mb-1">Your associated division</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">
                            @if(config('app.mode') == 'subdivision')
                                {{ $data['division'] }}/{{ $data['subdivision'] }}
                            @else
                                {{ $data['division'] }}
                            @endif
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-earth-europe fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ATC Hours card -->
    <div class="col-xl col-md-6 mb-4">
        <div class="card {{ ($atcHours < Setting::get('atcActivityRequirement', 10)) ? 'border-left-danger' : 'border-left-success' }} shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col me-2">
                        <div class="fs-sm fw-bold text-success text-uppercase mb-1">ATC Hours (Last {{ Setting::get("atcActivityQualificationPeriod") }} months)</div>
                        <div class="h5 mb-0 fw-bold text-gray-800">{{ $atcHours ? round($atcHours).' hours of '.Setting::get("atcActivityRequirement").' required' : 'N/A' }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Last training card -->
    <div class="col-xl col-md-6 mb-4 d-none d-xl-block d-lg-block d-md-block">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col me-2">
                        <div class="fs-sm fw-bold text-info text-uppercase mb-1">My last training</div>
                        <div class="row g-0 align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 me-3 fw-bold text-gray-800">{{ $data['report'] != null ? $data['report']->report_date->toEuropeanDate() : "-" }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($nextRoster)
    <!-- Next roster -->
    <div class="col-xl col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col me-2">
                        <div class="fs-sm fw-bold text-warning text-uppercase mb-1">My next event roster</div>
                        <div class="row g-0 align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 me-3 fw-bold text-gray-800">{{ ($nextRoster->position)?$nextRoster->position->code:'Backup' }} - {{Carbon\Carbon::parse($nextRoster->from)->format('H:i')}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fa-solid fa-calendar-day fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<div class="row event-row">
    <div class="col-12">
        <div class="card shadow mb-4 d-block">
            <!-- Card Header - Dropdown -->
            <div class="card-header bg-primary py-0 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white my-3">Events</h6>
                @can('events.manage')
                    <a href="{{ route('event.create') }}" class="btn btn-success py-1">New event</a>
                @endcan
            </div>
            <!-- Card Body -->
            <div class="card-body p-0">
                @if(count($events) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-leftpadded mb-0" width="100%" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Date and time</th>
                                    <th>Reported</th>
                                    <th>Roster published</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($events as $e)
                                <tr>
                                    <td style="vertical-align: middle;">
                                        <a href="" eid="{{ $e->id }}" @click.prevent="getEventData({{$e->id}})">{{ $e->name }}</a>
                                        @if(!$e->notification_sent)
                                            <span class="text-danger ps-2">Not published</span>
                                        @endif
                                    </td>
                                    <td style="vertical-align: middle;">
                                        {{ $e->timeLength }}</td>
                                    <td style="vertical-align: middle;">
                                        @if($e->isUserAvailable(Auth::user()))
                                            <div class="d-flex align-items-center">
                                                <i class="fa-solid fa-circle-check text-success" style="font-size:1.3em;"></i>
                                                <a href="{{ route('event.avl.edit', $e->id) }}" class="ms-3 btn btn-outline-success">Edit availability</a>
                                            </div>
                                        @elseif($e->hasUserReported(Auth::user()))
                                            <div class="d-flex align-items-center">
                                                <i class="fa-solid fa-circle-minus text-warning" style="font-size:1.3em;"></i>
                                                <a href="{{ route('event.avl.edit', $e->id) }}" class="ms-3 btn btn-outline-success">Edit availability</a>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center">
                                                <i class="fa-solid fa-circle-xmark text-danger" style="font-size:1.3em;"></i>
                                                <a href="{{ route('event.avl.create', $e->id) }}" class="ms-3 btn btn-success">Report availability</a>
                                            </div>
                                        @endif
                                    </td>
                                    <td style="vertical-align: middle;">
                                        {!! $e->roster_published?'<i class="fa-solid fa-circle-check text-success" style="font-size:1.3em;"></i>':'<i class="fa-solid fa-circle-xmark text-danger" style="font-size:1.3em;"></i>' !!}
                                    </td>

                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                <div class="d-flex align-items-center justify-content-center">
                    <p class="my-4">There are no events planned</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true" ref="eventModal">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="card shadow d-block">
                        <!-- Card Header - Dropdown -->
                        <div class="card-header bg-primary py-0 d-flex flex-row align-items-center justify-content-end">
                            <h6 class="m-0 fw-bold text-white my-3 eventName me-auto">Event name</h6>
                            @can('events.manage')
                                <a class="btn btn-success mx-1 editRoster">Edit roster</a>
                                <a class="btn btn-info mx-1 updateEvent">Update event</a>
                                <a class="btn btn-success d-none mx-1 publishEvent">Publish event</a>
                            @endcan
                        </div>
                        <!-- Card Body -->
                        <div class="card-body" style="max-height:85vh;overflow:auto;">
                            <div class="row mb-3">
                                <div class="col-12 col-lg-4">
                                    <div class="w-100 bg-secondary" style="aspect-ratio:16/9;">
                                        <img src="" alt="event cover image" class="w-100 eventImage" style="aspect-ratio:16/9;object-fit:contain;">
                                    </div>
                                </div>
                                <div class="col-12 col-lg-8 eventDescription placeholder-glow">
                                    <span class="placeholder col-7"></span>
                                </div>
                            </div>
                            <div class="row my-3">
                                <div class="col-12 col-lg-3 text-center border-lg-end border-secondary d-flex justify-content-center flex-column">
                                    Report status: <div class="d-block text-center reportedStatus placeholder-glow"><span class="badge text-bg-danger placeholder">Not reported</span></div>
                                </div>
                                <div class="col-12 col-lg-3 text-center border-lg-end border-secondary d-flex justify-content-center flex-column">
                                    Report availability: <div class="d-block text-center reportedAvail placeholder-glow"><span class="badge text-bg-danger placeholder">Not reported</span></div>
                                </div>
                                <div class="col-12 col-lg-3 text-center border-lg-end border-secondary d-flex justify-content-center flex-column">
                                    Report time: <div class="d-block text-center reportedTime placeholder-glow"><span class="badge text-bg-danger placeholder">Not reported</span></div>
                                </div>
                                <div class="col-12 col-lg-3 text-center d-flex justify-content-center flex-column">
                                    Your roster: <div class="d-block text-center userRoster placeholder-glow"><span class="badge text-bg-danger placeholder">Not reported</span></div>
                                </div>
                            </div>


                            <div class="row mt-3 eventRosterContainer">
                                <span class="fs-3 text-center text-secondary border-bottom">Event roster</span>
                                <div class="eventRosterAll row"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="row">
    <!-- Area Chart -->
    <div class="col-xl-8 col-lg-7 ">

        @if(\Auth::user()->hasRole('mentor'))
        <div class="card shadow mb-4 d-none d-xl-block d-lg-block d-md-block">
            <!-- Card Header - Dropdown -->
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">My Students</h6>
            </div>
            <!-- Card Body -->
            <div class="card-body {{ sizeof($studentTrainings) == 0 ? '' : 'p-0' }}">

                @if (sizeof($studentTrainings) == 0)
                <p class="mb-0">You have no students.</p>
                @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-leftpadded mb-0" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Level</th>
                                <th>Area</th>
                                <th>State</th>
                                <th>Last Training</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studentTrainings as $training)
                            <tr>
                                <td><a href="{{ $training->path() }}">{{ $training->user->name }}</a></td>
                                <td>
                                    <i class="{{ $types[$training->type]["icon"] }} text-primary"></i>
                                    @foreach($training->ratings as $rating)
                                    @if ($loop->last)
                                    {{ $rating->name }}
                                    @else
                                    {{ $rating->name . " + " }}
                                    @endif
                                    @endforeach
                                </td>
                                <td>{{ $training->area->name }}</td>
                                <td>
                                    <i class="{{ $training->status->icon() }} text-{{ $training->status->color() }}"></i>&ensp;{{ $training->status->label() }}{{ isset($training->paused_at) ? ' (PAUSED)' : '' }}
                                </td>
                                <td>
                                    @if($training->reports->count() > 0)
                                        @php
                                            $reportDate = Carbon\Carbon::make($training->reports->sortBy('report_date')->last()->report_date);
                                            $trainingIntervalExceeded = $reportDate->diffInDays() >= Setting::get('trainingInterval');
                                        @endphp
                                        <span title="{{ $reportDate->toEuropeanDate() }}">
                                            @if($reportDate->isToday())
                                            <span class="{{ ($trainingIntervalExceeded && $training->status !== \App\Helpers\TrainingStatus::AWAITING_EXAM && !$training->paused_at) ? 'text-danger' : '' }}">Today</span>
                                            @elseif($reportDate->isYesterday())
                                            <span class="{{ ($trainingIntervalExceeded && $training->status !== \App\Helpers\TrainingStatus::AWAITING_EXAM && !$training->paused_at) ? 'text-danger' : '' }}">Yesterday</span>
                                            @elseif($reportDate->diffInDays() <= 7)
                                            <span class="{{ ($trainingIntervalExceeded && $training->status !== \App\Helpers\TrainingStatus::AWAITING_EXAM && !$training->paused_at) ? 'text-danger' : '' }}">{{ $reportDate->diffForHumans(['parts' => 1]) }}</span>
                                            @else
                                            <span class="{{ ($trainingIntervalExceeded && $training->status !== \App\Helpers\TrainingStatus::AWAITING_EXAM && !$training->paused_at) ? 'text-danger' : '' }}">{{ $reportDate->diffForHumans(['parts' => 2]) }}</span>
                                            @endif

                                        </span>
                                    @else
                                        No registered training yet
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endif

        <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">My Trainings</h6>
            </div>
            <!-- Card Body -->
            <div class="card-body {{ $trainings->count() == 0 ? '' : 'p-0' }}">

                @if ($trainings->count() == 0)
                <p>You have no registered trainings.</p>
                @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-leftpadded mb-0" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>Level</th>
                                <th>Area</th>
                                <th>Period</th>
                                <th>State</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trainings as $training)
                            <tr>
                                <td>
                                    <a href="{{ $training->path() }}">
                                        @foreach($training->ratings as $rating)
                                        @if ($loop->last)
                                        {{ $rating->name }}
                                        @else
                                        {{ $rating->name . " + " }}
                                        @endif
                                        @endforeach
                                    </a>
                                </td>
                                <td>{{ $training->area->name }}</td>
                                <td>
                                    @if ($training->started_at == null && $training->closed_at == null)
                                    Training not started
                                    @elseif ($training->closed_at == null)
                                    {{ $training->started_at->toEuropeanDate() }} -
                                    @elseif ($training->started_at != null)
                                    {{ $training->started_at->toEuropeanDate() }} - {{ $training->closed_at->toEuropeanDate() }}
                                    @else
                                    N/A
                                    @endif
                                </td>
                                <td>
                                    <i class="{{ $training->status->icon() }} text-{{ $training->status->color() }}"></i>&ensp;{{ $training->status->label() }}{{ isset($training->paused_at) ? ' (PAUSED)' : '' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">Request Training</h6>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="text-center">
                    <img class="img-fluid px-3 px-sm-4 mb-4" style="width: 25rem;" src="images/undraw_speech_to_text_vatsim.svg" alt="">
                </div>
                <p>Are you interested in becoming an Air Traffic Controller? Wish to receive training for a higher rating? Request training below and you will be notified when a space is available.</p>

                @can('apply', \App\Models\Training::class)
                <div class="d-grid">
                    <a href="{{ route('training.apply') }}" class="btn btn-success">
                        Request training
                    </a>
                </div>
                @else

                <div class="btn btn-{{ (\Auth::user()->hasActiveTrainings(true) && Setting::get('trainingEnabled')) ? 'success' : 'primary' }} d-block disabled not-allowed" role="button" aria-disabled="true">
                    @if(\Auth::user()->hasActiveTrainings(true) && Setting::get('trainingEnabled'))
                    <i class="fas fa-check"></i>
                    @else
                    <i class="fas fa-exclamation-triangle"></i>
                    @endif
                    {{ Gate::inspect('apply', \App\Models\Training::class)->message() }}
                </div>

                @if(Setting::get('trainingEnabled'))
                <div class="alert alert-primary" role="alert">
                    <p class="small">
                        <b><i class="fas fa-chevron-right"></i> How do I join the division?</b>
                        <a href="{{ Setting::get('linkJoin') }}" target="_blank">Read about joining here. You will be able to apply here within 24 hours after transfer.</a>

                        <br>

                        <b><i class="fas fa-chevron-right"></i> How to apply to be a visiting controller?</b>
                        <a href="{{ Setting::get('linkVisiting') }}" target="_blank">Check this page for more information.</a>

                        <br>

                        <b><i class="fas fa-chevron-right"></i> My rating is inactive?</b>
                        <a href="{{ Setting::get('linkContact') }}" target="_blank">Contact local training staff for refresh or transfer training.</a>

                        <br>

                        <b><i class="fas fa-chevron-right"></i> How long is the queue?</b>
                        {{ \Auth::user()->getActiveTraining()->area->waiting_time ?? 'See application page or training confirmation email for details.' }}
                    </p>
                </div>
                @endif

                @endcan
            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
@vite('resources/js/vue.js')
<script>
    document.addEventListener("DOMContentLoaded", function () {

        const hasElevatedAccess = {{ auth()->user()->hasPermission('events.manage') ? 'true' : 'false' }};

        const eventModalId = '#eventModal';

        const reportedStatusYes = `<span class="badge text-bg-success">Reported</span>`;
        const reportedStatusNo = `<span class="badge text-bg-danger">Not reported</span>`;

        const reportedAvailYes = `<span class="badge text-bg-success">Available</span>`;
        const reportedAvailNo = `<span class="badge text-bg-warning">Not available</span>`;

        const reportedTime = `<span class="badge text-bg-info text-light">{0}</span>`;

        const userRosterYes = `<span class="badge text-bg-success">{0}</span>`;
        const userRosterNo = `<span class="badge text-bg-secondary">Not on the roster</span>`;

        const rosterNotPublished = `<span class="badge text-bg-secondary">Roster not published</span>`;

        const placeholder = `<span class="placeholder col-7"></span>`;

        const placeholderBadge = `<span class="placeholder col-3"></span>`;

        const imageURL = `/storage/images/{0}`;
        const updateEventURL = `{{ route('event.edit', '-1') }}`;
        const publishEventURL = `{{ route('event.publish', '-1') }}`;
        const editRosterURL = `{{ route('event.roster.create', '-1') }}`;

        const rosterAirport = `<div class="col-12 col-lg-6 col-xl-4 p-2">
                                    <ul class="list-group">
                                        <li class="list-group-item bg-primary text-light text-center">{airportCode}</li>
                                        {positions}
                                    </ul>
                                </div>`;

        const rosterPosition = `<li class="list-group-item list-group-item-primary">
                                            <div class="d-flex w-100 justify-content-between">
                                              <h5 class="mb-1">{userName} - {userCid}</h5>
                                              <small class="text-body-secondary">{positionCode}</small>
                                            </div>
                                            <p class="mb-1">{rosterStart} - {rosterEnd}</p>
                                            <small class="text-body-secondary">{callsign} {frequency}</small>
                                            {mentors}
                                        </li>`;

        const rosterMentorContainer = `<div class="d-flex flex-column align-items-end">
                                                {content}
                                            </div>`;

        const rosterMentor = `<span class="badge rounded-pill bg-primary mt-1">
                                                    {description}: {userName} - {userCid}
                                                </span>`;

        const events = createApp({
            data() {
                return {
                    name: null,
                    date: null,
                    startTime: null,
                    endTime: null,
                    description: null,
                    notes: null,
                    coverImage: null,
                    eventURL: `{{route('event.api.data', '-1')}}`,
                }
            },
            methods:{
                getEventData(eventId) {

                    document.querySelector(eventModalId + ' .eventName').innerHTML=placeholder;
                    document.querySelector(eventModalId + ' .eventDescription').innerHTML=placeholder;
                    document.querySelector(eventModalId + ' .eventImage').setAttribute('src', '');
                    document.querySelector(eventModalId + ' .eventImage').classList.add("d-none");
                    document.querySelector(eventModalId + ' .eventRosterAll').innerHTML = '';
                    document.querySelector(eventModalId + ' .eventRosterContainer').classList.add("d-none");

                    if(document.querySelector(eventModalId + ' .publishEvent'))
                        document.querySelector(eventModalId + ' .publishEvent').classList.add('d-none');

                    if(document.querySelector(eventModalId + ' .editRoster'))
                        document.querySelector(eventModalId + ' .editRoster').classList.add('d-none');

                    if(document.querySelector(eventModalId + ' .updateEvent'))
                        document.querySelector(eventModalId + ' .updateEvent').setAttribute('href', '');

                    document.querySelector(eventModalId + ' .reportedStatus').innerHTML=placeholderBadge;
                    document.querySelector(eventModalId + ' .reportedAvail').innerHTML=placeholderBadge;
                    document.querySelector(eventModalId + ' .reportedTime').innerHTML=placeholderBadge;
                    document.querySelector(eventModalId + ' .userRoster').innerHTML=placeholderBadge;

                    const modal = new bootstrap.Modal('#eventModal');
                    modal.show();

                    const xhttp = new XMLHttpRequest();

                    xhttp.onload = function() {
                        const eventData = JSON.parse(this.responseText);

                        if(!eventData.success) return;

                        document.querySelector(eventModalId + ' .eventName').innerHTML=eventData.data.name;
                        document.querySelector(eventModalId + ' .eventDescription').innerHTML=eventData.data.description;
                        document.querySelector(eventModalId + ' .eventImage').setAttribute('src', imageURL.replace('{0}', eventData.data.cover_image));
                        document.querySelector(eventModalId + ' .eventImage').classList.remove("d-none");

                        if(document.querySelector(eventModalId + ' .updateEvent'))
                            document.querySelector(eventModalId + ' .updateEvent').setAttribute('href', updateEventURL.replace('-1', eventData.data.id));

                        if(document.querySelector(eventModalId + ' .publishEvent'))
                            document.querySelector(eventModalId + ' .publishEvent').setAttribute('href', publishEventURL.replace('-1', eventData.data.id));

                        if(!eventData.data.notification_sent)
                            document.querySelector(eventModalId + ' .publishEvent').classList.remove('d-none');
                        else if(document.querySelector(eventModalId + ' .editRoster')){
                            document.querySelector(eventModalId + ' .editRoster').classList.remove('d-none');
                            document.querySelector(eventModalId + ' .editRoster').setAttribute('href', editRosterURL.replace('-1', eventData.data.id));
                        }

                        if(!eventData.user_data){
                            document.querySelector(eventModalId + ' .reportedStatus').innerHTML=reportedStatusNo;
                            document.querySelector(eventModalId + ' .reportedAvail').innerHTML=reportedStatusNo;
                            document.querySelector(eventModalId + ' .reportedTime').innerHTML=reportedStatusNo;
                        } else {
                            document.querySelector(eventModalId + ' .reportedStatus').innerHTML=reportedStatusYes;
                            document.querySelector(eventModalId + ' .reportedAvail').innerHTML=(eventData.user_data.available)?reportedAvailYes:reportedAvailNo;
                            document.querySelector(eventModalId + ' .reportedTime').innerHTML=(eventData.user_data.available)?reportedTime.replace('{0}', eventData.user_data.start+'-'+eventData.user_data.end):reportedAvailNo;
                        }

                        if(eventData.user_roster && eventData.user_roster.length > 0){
                            var html = "";
                            eventData.user_roster.forEach((roster) => {

                                var entry = `${(roster.position)?roster.position.code:'Backup'} ${roster.from}-${roster.to}`;

                                if(roster.mentors && roster.mentors.length > 0){
                                    roster.mentors.forEach((mentor) => {
                                        entry += `<br>${(mentor.description)?mentor.description:'Mentor'}: ${mentor.user.display_name} [${mentor.user.id}]`;
                                    });
                                }

                                html += userRosterYes.replace('{0}', entry);

                            });

                            document.querySelector(eventModalId + ' .userRoster').innerHTML=html;

                        } else if(!eventData.user_data){
                            document.querySelector(eventModalId + ' .userRoster').innerHTML=reportedStatusNo;
                        } else if(eventData.data.roster_published){
                            document.querySelector(eventModalId + ' .userRoster').innerHTML=userRosterNo;
                        } else {
                            document.querySelector(eventModalId + ' .userRoster').innerHTML=rosterNotPublished;
                        }

                        if(eventData.data.roster_published){
                            const event = eventData.data;

                            var airports = [];

                            event.rosters.forEach(r => {
                                const airportCode = (r.position)?r.position.code.split('_')[0]:'Backup';

                                var html = rosterPosition;

                                var name = `${r.user.display_name}`;

                                if(hasElevatedAccess){
                                    name = `<a href="/user/${r.user.id}">${name}</a>`;
                                }

                                html = html.replace('{userName}', name);
                                html = html.replace('{userCid}', r.user.id);
                                html = html.replace('{positionCode}', (r.position)?r.position.code:'');
                                html = html.replace('{rosterStart}', r.from);
                                html = html.replace('{rosterEnd}', r.to);

                                html = html.replace('{callsign}', (r.position)?r.position.callsign:'');

                                if(r.position && r.position.frequency)
                                    html = html.replace('{frequency}', '- ' + r.position.frequency);
                                else
                                    html = html.replace('{frequency}', '');

                                var mentorshtml = '';
                                var current_mentor = false;
                                r.mentors.forEach(m => {
                                    mentorshtml += rosterMentor.replace('{description}', (m.description)?m.description:'Mentor').replace('{userName}', m.user.display_name).replace('{userCid}', m.user.id);
                                    if(m.is_current_user)
                                        current_mentor = true;
                                });

                                html = html.replace('{mentors}', rosterMentorContainer.replace('{content}', mentorshtml));

                                if(current_mentor)
                                    html = html.replace('list-group-item-primary', 'list-group-item-warning');
                                else if(!r.is_current_user)
                                    html = html.replace('list-group-item-primary', '');

                                airports[airportCode] = ((airports[airportCode])?airports[airportCode]:'') + html;
                            });

                            var html = '';

                            for (var key in airports) {
                                const val = airports[key];
                                html += rosterAirport.replace('{airportCode}', key).replace('{positions}', val);
                            }

                            document.querySelector(eventModalId + ' .eventRosterAll').innerHTML = html;
                            document.querySelector(eventModalId + ' .eventRosterContainer').classList.remove("d-none");
                        }

                    }

                    xhttp.open("GET", this.eventURL.replace('-1', eventId), true);
                    xhttp.send();

                },
                isNumeric(value) {
                    return /^-?\d+$/.test(value);
                }
            },
            mounted(){
                const params = new Proxy(new URLSearchParams(window.location.search), {
                    get: (searchParams, prop) => searchParams.get(prop),
                });
                // Get the value of "some_key" in eg "https://example.com/?some_key=some_value"
                let e = params.event;

                if(e){
                    window.history.replaceState({}, document.title, window.location.href.substring(0, window.location.href.indexOf('?')));

                    if(this.isNumeric(e)){
                        this.getEventData(parseInt(e));
                    }
                }
            },
        }).mount('.row.event-row');
    });

</script>
@endsection
