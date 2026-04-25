@extends('layouts.app')

@section('title', 'Feedback' . ($feedbackUser ? ' for ' . $feedbackUser->name : ''))

@section('header')
    @vite(['resources/sass/bootstrap-table.scss', 'resources/js/bootstrap-table.js'])
@endsection

@section('content')
<div class="card shadow mb-4">
    <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 fw-bold text-white">
            Feedback {{ $feedbackUser ? 'for ' . $feedbackUser->name : "" }}
        </h6>
    </div>
    <div class="card-body {{ $feedback->count() == 0 ? '' : 'p-0 px-2' }}">

        @if($feedback->count() == 0)
            <p class="mb-0 text-center">No feedback found</p>
        @else
            <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
            @foreach ($feedback as $f)
                <div class="card mb-3 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
                    @if(!$f->acknowledged && !$feedbackUser)
                        <div class="position-absolute top-0 start-0 bottom-0" style="width:3px; background:#378ADD;"></div>
                    @endif

                    <div class="card-body ps-4">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 flex-wrap">
                        <div class="d-flex flex-wrap gap-2">
                            @if($f->referenceUser)
                                <a style="font-family:'DM Mono',monospace;font-size:10.5px;" href="{{ route('user.show', $f->referenceUser->id) }}"><span class="badge rounded-pill border text-body" style="font-family:'DM Mono',monospace;font-size:10.5px;text-decoration:underline;">{{ $f->referenceUser->name }} · {{ $f->referenceUser->id }}</span></a>
                            @endif
                            @if($f->referencePosition)
                                <span class="badge rounded-pill" style="background:#EEEDFE;color:#3C3489;font-family:'DM Mono',monospace;font-size:10.5px;">{{ $f->referencePosition->callsign }}</span>
                            @endif
                            @if($f->time)
                                <span class="badge rounded-pill" style="background:#FAEEDA;color:#633806;font-family:'DM Mono',monospace;font-size:10.5px;">Time: {{ $f->time }}</span>
                            @endif
                        </div>
                        <small class="text-muted" style="font-family:'DM Mono',monospace;font-size:11px;">{{ Carbon\Carbon::parse($f->created_at)->format('d M Y · H:i\z') }}</small>
                        </div>

                        <p class="mb-3" style="white-space: pre-wrap;font-size:13.5px;line-height:1.65;">{{ $f->feedback }}</p>

                        @if(!$feedbackUser)
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                style="width:24px;height:24px;background:#E6F1FB;color:#185FA5;font-size:9px;font-weight:500;">{{ str_replace('.', '', $f->submitter->getInitials()) }}</div>
                            <small class="text-secondary"><a href="{{ route('user.show', $f->submitter->id) }}">{{ $f->submitter->name }} <span class="text-muted">· {{ $f->submitter->id }}</span></a></small>
                        </div>
                        <div class="d-flex gap-2">
                            @if(!$f->acknowledged)
                                <button onclick="ackFeedback({{ $f->id }})" ack-id="{{ $f->id }}" class="btn btn-sm rounded-3" style="background:#EAF3DE;color:#3B6D11;border:0.5px solid #97C459;font-size:12px;">Acknowledge</button>
                            @endif
                            @if(!$f->published)
                                <button onclick="pubFeedback({{ $f->id }})" pub-id="{{ $f->id }}" class="btn btn-sm rounded-3" style="background:#E6F1FB;color:#185FA5;border:0.5px solid #85B7EB;font-size:12px;">Publish</button>
                            @endif
                        </div>
                        </div>
                        @endif
                    </div>
                    @if($f->acknowledged && !$feedbackUser)
                        <div class="position-absolute top-0 end-0 bottom-0" style="width:3px; background:{{ $f->published ? '#3B6D11' : '#AAAAAA' }};"></div>
                    @endif
                </div>
            @endforeach
        @endif

    </div>

    <div class="mt-3 d-flex align-middle justify-content-center">
        {{ $feedback->links() }}
    </div>
</div>
@endsection

@section('js')
@if(!$feedbackUser)
<script>
    function ackFeedback(id){
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            if(xhttp.status == 200){
                location.reload();
            }
        }
        xhttp.open("POST", "{{ route('feedback.acknowledge') }}", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send(`feedback_id=${id}&_token={{ csrf_token() }}`);
    }

    function pubFeedback(id){
        console.log(id);
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            if(xhttp.status == 200){
                location.reload();
            }
        }
        xhttp.open("POST", "{{ route('feedback.publish') }}", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send(`feedback_id=${id}&_token={{ csrf_token() }}`);
    }
</script>
@endif
@endsection
