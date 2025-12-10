@extends('layouts.app')

@section('title', 'Report availability')
@section('content')

<div class="row">
    <div class="col col-lg-8">

        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-white">
                    Report availability
                </h6> 
            </div>
            <div class="card-body">
                <form action="{{ route('event.avl.store', $event->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input id="hidden_start" class="form-control" type="hidden" value="{{Carbon\Carbon::parse($event->start)->format("H:i\z")}}">
                    <input id="hidden_end" class="form-control" type="hidden" value="{{Carbon\Carbon::parse($event->end)->format("H:i\z")}}">

                    <div class="form-group mb-2">
                        <label for="event_name">Event name</label>
                        <input type="text" id="event_name" class="form-control" name="event_name" required readonly value="{{$event->name}} | {{Carbon\Carbon::parse($event->start)->format("d.m.Y H:i")}} - {{Carbon\Carbon::parse($event->end)->format("H:i")}}">
                    </div>
                    <div>
                        <strong>Message for controllers</strong>
                        @if($event->controller_message) {!!$event->controller_message!!} @else / @endif
                    </div>

                    <div class="form-group form-check">
                        <input value="true" type="checkbox" class="form-check-input" id="avlb" name="available" checked onchange="changeAvlb()">
                        <label class="form-check-label" for="avlb">Available</label>
                    </div>

                    <div class="row form-group mb-3" id="time">
                        <div class="col">
                            <label for="start_at">Start</label>
                            <input id="start_at" class="form-control @error('start_at') is-invalid @enderror" type="text" name="start_at" value="{{Carbon\Carbon::parse($event->start)->format("H:i\z")}}" required onChange="checkTime()">
                            @error('start_at')
                                <span class="text-danger">{{ $errors->first('start_at') }}</span>
                            @enderror
                        </div>
                        <div class="col">
                            <label for="end_at">End</label>
                            <input id="end_at" class="form-control @error('end_at') is-invalid @enderror" type="text" name="end_at" value="{{Carbon\Carbon::parse($event->end)->format("H:i\z")}}" required onChange="checkTime()">
                            @error('end_at')
                                <span class="text-danger">{{ $errors->first('end_at') }}</span>
                            @enderror
                        </div>
                    </div> 
                    
                    <button type="submit" class="btn btn-success">Report availability</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@section('js')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
window.addEventListener('load', function(){
    document.getElementById('start_at').flatpickr({ disableMobile: true, enableTime: true, noCalendar: true, dateFormat: "H:i\z", time_24hr: true});
    document.getElementById('end_at').flatpickr({ disableMobile: true, enableTime: true, noCalendar: true, dateFormat: "H:i\z", time_24hr: true});
});
function changeAvlb(){
    if(document.getElementById('avlb').checked) {
        document.getElementById('time').classList.remove("d-none");
    }
    else {
        document.getElementById('time').classList.add("d-none");
        let start  = document.getElementById("hidden_start").value;
        let end  = document.getElementById("hidden_end").value;
        document.getElementById("start_at").value = start.trim();
        document.getElementById("end_at").value = end.trim();
    }
}
</script>
@endsection


