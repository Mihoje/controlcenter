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
    <div class="card-body {{ $feedback->count() == 0 ? '' : 'p-0' }}">

        @if($feedback->count() == 0)
            <p class="mb-0 text-center">No feedback found</p>
        @else
            <div class="list-group d-flex flex-column row-gap-3">
                @foreach ($feedback as $f)
                    <div class="rounded list-group-item list-group-item-action {{ $f->acknowledged ? "" : "list-group-item-info" }}">
                        <div class="d-flex w-100 justify-content-between pb-3">
                            <h5 class="mb-1 flex-fill text-center"><a class="{{ $f->referenceUser ? "" : "text-decoration-none" }}" href="{{ $f->referenceUser ? route('user.show', $f->referenceUser->id) : "" }}">{{ $f->header }}</a></h5>
                            <small>{{ Carbon\Carbon::parse($f->created_at)->format('d/m/Y H:i') }}</small>
                        </div>
                        <p class="mb-4" style="white-space: pre-wrap;">{{ $f->feedback }}</p>
                        @if(!$feedbackUser && auth()->user()->isAdmin())
                            <div class="d-flex w-100 justify-content-between">
                                <small class="d-block text-center flex-fill align-self-center">Submitted by: <a href="{{ route('user.show', $f->submitter->id) }}">{{ $f->footer }}</a></small>
                                <button onclick="ackFeedback({{ $f->id }})" ack-id="{{ $f->id }}" class="btn btn-success {{ $f->acknowledged ? "disabled" : "" }}">Acknowledge</button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

    </div>

    <div class="mt-3 d-flex align-middle justify-content-center">
        {{ $feedback->links() }}
    </div>
</div>
@endsection

@section('js')
<script>
    function ackFeedback(id){
        console.log(id);

        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            if(xhttp.status == 200){
                const btn = document.querySelector(`button[ack-id="${id}"]`);

                if(!btn){
                    return;
                }

                btn.classList.add('disabled');

                btn.parentNode.parentNode.classList.remove('list-group-item-info');
            }
        }
        xhttp.open("POST", "{{ route('feedback.acknowledge') }}", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send(`feedback_id=${id}&_token={{ csrf_token() }}`);
    }
</script>
@endsection
