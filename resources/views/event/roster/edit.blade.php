@extends('layouts.app')

@section('title', 'Edit roster for ' . $event->name . ' | ' . $event->date)

@section('header')
    @vite(['resources/sass/bootstrap-table.scss', 'resources/js/bootstrap-table.js'])
    
<style>
    .event-position-selector > div{
        transition: background-color .2s;
        cursor: pointer;
    }
    .event-position-selector > div:hover{
        background-color: #eee;
    }
    .userListItem{
        cursor: pointer;
    }
    .event-position-selector{
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div id="roster">
<div class="row">
    <div class="col-12 col-xl-5 mb-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">User availability ({{ $avail->where('available', true)->count() }} available)</h6> 
            </div>        
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0" style="min-width:max-content;">
                        <thead>
                            <tr>
                                <th scope="col">CID</th>
                                <th scope="col">Name</th>
                                <th scope="col"><div class="text-center">Rating</div></th>
                                <th scope="col"><div class="text-center">Available</div></th>
                                <th scope="col"><div class="text-center">Time</div></th>
                                <th scope="col"><div class="text-center">Applied at</div></th>
                                <th scope="col"><div class="text-center">Updated at</div></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($avail as $a)
                                <tr>
                                    <td scope="row">{{ $a->user->id }}</td>
                                    <td>{{ $a->user->name }}</td>
                                    <td class="text-center">{{ $a->user->rating_short }}</td>
                                    <td class="text-center">
                                        @if($a->available)
                                            <i class="fa-solid fa-check-double text-success"></i>
                                        @else 
                                            <i class="fa-solid fa-xmark text-danger"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($a->available)
                                            {{ $a->start }}&nbsp;-&nbsp;{{ $a->end }}
                                        @else
                                            /
                                        @endif
                                    </td>
                                    <td>{!! Carbon\Carbon::parse($a->created_at)->format('dS M @ H:i') !!}</td>
                                    <td>{!! Carbon\Carbon::parse($a->updated_at)->format('dS M @ H:i') !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col mb-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">Roster</h6> 
            </div>        
            <div class="card-body p-0">
                <form action="" method="POST" id="rosterForm">
                    @csrf

                    <div id="eventPositions">
                        
                        @php
                            $i = 0;
                        @endphp

                        
                        @foreach($rosterGroups as $rg)
                            <div class="d-flex flex-column event-position" position-id="{{ $rg[0]->position->id }}">
                                <div class="p-1">
                                    <span class="fs-6 ms-1">{{ $rg[0]->position->callsign }} - <span class="fw-bold">{{ $rg[0]->position->code }}</span></span>
                                    <a type="button" class="btn btn-outline-success ms-2" onclick="addAdditionalController({{ $rg[0]->position->id }})">Add</a>
                                </div>
                                @foreach($rg as $r)
                                    <div class="p-1 d-flex flex-md-row flex-column justify-content-around controllerPositionGroup" field-id="{{ $i }}">
                                        <input type="hidden" name="posid-{{ $i }}" value="{{ $r->position->id }}">
                                        <div class="form-group px-1">
                                            <label for="_controller-{{ $i }}">Controller</label>
                                            <input type="hidden" name="controller-{{ $i }}" id="controller-{{ $i }}" field-id="{{ $i }}" value="{{ ($r->user)?$r->user->id:'' }}">
                                            <input type="text" name="_controller-{{ $i }}" id="_controller-{{ $i }}"  field-id="{{ $i }}" value="{{ ($r->user)?$r->user->first_name:'' }} {{ ($r->user)?$r->user->last_name:'' }}" class="form-control formController" onfocus="controllerNameFocus()"  onblur="controllerNameBlur()" readonly>
                                        </div>
                                        <div class="form-group px-1">
                                            <label for="start-{{ $i }}">Start</label>
                                            <input type="text" name="start-{{ $i }}" id="start-{{ $i }}" value="{{$r->from}}z" class="form-control formStart time-init" style="width:100px">
                                        </div>
                                        <div class="form-end px-1">
                                            <label for="start-{{ $i }}">End</label>
                                            <input type="text" name="end-{{ $i }}" id="end-{{ $i }}" value="{{$r->to}}z" class="form-control formEnd time-init" style="width:100px">
                                        </div>
                                        <div class="form-group px-1">
                                            <label for="_mentor-{{ $i }}">Mentor</label>

                                            @php
                                                $mentors = '';
                                                $mentorsText = '';
                                                foreach ($r->mentors as $m) {
                                                    $mentors .= $m->user->id . ',';
                                                    $mentorsText .= $m->user->first_name . ' ' . $m->user->last_name . ', ';
                                                }

                                                if(strlen($mentors) > 0){
                                                    $mentors = substr($mentors, 0, strlen($mentors) - 1);
                                                    $mentorsText = substr($mentorsText, 0, strlen($mentorsText) - 2);
                                                }
                                            @endphp

                                            <input type="hidden" name="mentor-{{ $i }}" id="mentor-{{ $i }}" field-id="{{ $i }}" value="{{$mentors}}">
                                            <input type="text" name="_mentor-{{ $i }}" id="_mentor-{{ $i }}" field-id="{{ $i }}" value="{{$mentorsText}}" class="form-control formMentor" onfocus="mentorNameFocus()"  onblur="mentorNameBlur()" readonly>
                                        </div>
                                        <div class="d-flex align-items-end" style="width:fit-content;">
                                            <a class="btn btn-outline-danger" onclick="removeRow({{$i}})">X</a>
                                        </div>
                                    </div>
                                    @php
                                        $i++;
                                    @endphp
                                @endforeach
                            </div>
                        @endforeach

                    </div>
                    <div class="d-flex flex-column event-position" position-id="/1">
                        <div class="p-1">
                            <span class="fs-6 ms-1">Backup</span>
                            <a type="button" class="btn btn-outline-success ms-2" onclick="addAdditionalController('/1')">Add</a>
                        </div>
                        @foreach($backupRoster as $r)
                            <div class="p-1 d-flex flex-md-row flex-column justify-content-around controllerPositionGroup" field-id="{{ $i }}">
                                <input type="hidden" name="posid-{{$i}}" value="/1">
                                <div class="form-group px-1">
                                    <label for="_controller-{{$i}}">Controller</label>
                                    <input type="hidden" name="controller-{{$i}}" id="controller-{{$i}}" field-id="{{$i}}" value="{{($r->user)?$r->user->id:''}}">
                                    <input type="text" name="_controller-{{$i}}" id="_controller-{{$i}}"  field-id="{{$i}}" value="{{($r->user)?$r->user->first_name:''}} {{($r->user)?$r->user->last_name:''}}" class="form-control formController" onfocus="controllerNameFocus()"  onblur="controllerNameBlur()" readonly>
                                </div>
                                <div class="form-group px-1">
                                    <label for="start-1">Start</label>
                                    <input type="text" name="start-{{$i}}" id="start-{{$i}}" value="{{$r->from}}z" class="form-control formStart time-init" style="width:100px">
                                </div>
                                <div class="form-end px-1">
                                    <label for="start-1">End</label>
                                    <input type="text" name="end-{{$i}}" id="end-{{$i}}" value="{{$r->to}}z" class="form-control formEnd time-init" style="width:100px">
                                </div>
                                <div class="form-group px-1">
                                    <label for="_mentor-1">Mentor</label>
                                    <input type="hidden" name="mentor-{{$i}}" id="mentor-{{$i}}" field-id="{{$i}}">
                                    <input type="text" name="_mentor-{{$i}}" id="_mentor-{{$i}}" field-id="{{$i}}" class="form-control formMentor" disabled>
                                </div>
                                <div class="d-flex align-items-end" style="width:fit-content;">
                                    <a class="btn btn-outline-danger" onclick="removeRow({{$i}})">X</a>
                                </div>
                            </div>
                            @php
                                $i++;
                            @endphp
                        @endforeach
                    </div>


                    <div class="p-2">
                        <a class="btn btn-outline-success" onclick="submit(false)">Save</a>
                        <a class="btn btn-success" onclick="submit(true)">Save and publish</a>
                        <a class="btn btn-warning" href="{{ route('event.roster.book', $event->id) }}">Book positions</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">Event positions</h6> 
            </div>        
            <div class="card-body p-0">
                <div class="row align-items-center">
                    @foreach($positions as $code=>$posit)
                        <div class="col-12 col-lg-6 ps-4 pt-2">
                            <h5>{{ $code }}</h5>
                            <hr>
                            <div class="row">
                                @foreach($posit as $pos)
                                    <div class="col-6 col-xxl-4 event-position-selector" position-id="{{ $pos->id }}" onclick="togglePosition({{$pos->id}})">
                                        <div class="border-start border-danger ps-1 d-flex flex-column mx-2 my-2">
                                            <span class="fs-6">{{ $pos->callsign }}</span>
                                            <div class="d-flex flex-row pe-1">
                                                <span class="fs-6">{{ $pos->frequency }}</span>
                                                <span class="fs-6 ms-auto">{{ $pos->code }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div id="userList" class="d-flex flex-column d-none" style="max-height: 300px; overflow-y:auto; position: absolute; z-index: 1000">
    <ul class="list-group">
    </ul>
</div>
<div id="mentorList" class="d-flex flex-column d-none" style="max-height: 300px; overflow-y:auto; position: absolute; z-index: 1000">
    <div class="list-group">
    </div>
</div>
@endsection


@section('js')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>

    const formSubmitURL = `{{ route('event.roster.edit', $event->id) }}`;
    const formPublishURL = `{{ route('event.roster.edit', $event->id) }}?publish`;

    const listDOM = document.getElementById('userList');
    const mentorDOM = document.getElementById('mentorList');
    const positions = JSON.parse(`{!! json_encode($positions) !!}`);

    const avail = JSON.parse(`{!! str_replace('"', '\"', str_replace("\\r\\n", '', json_encode($avail))) !!}`); @php /*NE ZNAM STA SE DESAVA ALI RADI */ @endphp
    const event = JSON.parse(`{!! str_replace('"', '\"', str_replace("\\r\\n", '', json_encode($event))) !!}`); @php /*NE ZNAM STA SE DESAVA ALI RADI */ @endphp

    const positionHTML = `<div class="d-flex flex-column event-position" position-id="{posId}">
                        <div class="p-1">
                            <span class="fs-6 ms-1">{callsign} - <span class="fw-bold">{code}</span></span>
                            <a type="button" class="btn btn-outline-success ms-2" onclick="addAdditionalController({posId})">Add</a>
                        </div>
                        <div class="p-1 d-flex flex-md-row flex-column justify-content-around controllerPositionGroup" field-id="{id}">
                            <input type="hidden" name="posid-{id}" value="{posId}">
                            <div class="form-group px-1">
                                <label for="_controller-{id}">Controller</label>
                                <input type="hidden" name="controller-{id}" id="controller-{id}" field-id="{id}">
                                <input type="text" name="_controller-{id}" id="_controller-{id}"  field-id="{id}" class="form-control formController" onfocus="controllerNameFocus()"  onblur="controllerNameBlur()" readonly>
                            </div>
                            <div class="form-group px-1">
                                <label for="start-{id}">Start</label>
                                <input type="text" name="start-{id}" id="start-{id}" value="${event.start_time}z" class="form-control formStart" style="width:100px">
                            </div>
                            <div class="form-end px-1">
                                <label for="start-{id}">End</label>
                                <input type="text" name="end-{id}" id="end-{id}" value="${event.end_time}z" class="form-control formEnd" style="width:100px">
                            </div>
                            <div class="form-group px-1">
                                <label for="_mentor-{id}">Mentor</label>
                                <input type="hidden" name="mentor-{id}" id="mentor-{id}" field-id="{id}">
                                <input type="text" name="_mentor-{id}" id="_mentor-{id}" field-id="{id}" class="form-control formMentor" onfocus="mentorNameFocus()"  onblur="mentorNameBlur()" readonly>
                            </div>
                            <div class="d-flex align-items-end" style="width:fit-content;">
                                <a class="btn btn-outline-danger" onclick="removeRow({id})">X</a>
                            </div>
                        </div>
                    </div>`;

    const positionAddHTML = `
                        <div class="p-1 d-flex flex-md-row flex-column justify-content-around controllerPositionGroup" field-id="{id}">
                            <input type="hidden" name="posid-{id}" value="{posId}">
                            <div class="form-group px-1">
                                <label for="_controller-{id}">Controller</label>
                                <input type="hidden" name="controller-{id}" id="controller-{id}" field-id="{id}">
                                <input type="text" name="_controller-{id}" id="_controller-{id}"  field-id="{id}" class="form-control formController" onfocus="controllerNameFocus()"  onblur="controllerNameBlur()" readonly>
                            </div>
                            <div class="form-group px-1">
                                <label for="start-{id}">Start</label>
                                <input type="text" name="start-{id}" id="start-{id}" value="${event.start_time}z" class="form-control formStart" style="width:100px">
                            </div>
                            <div class="form-end px-1">
                                <label for="start-{id}">End</label>
                                <input type="text" name="end-{id}" id="end-{id}" value="${event.end_time}z" class="form-control formEnd" style="width:100px">
                            </div>
                            <div class="form-group px-1">
                                <label for="_mentor-{id}">Mentor</label>
                                <input type="hidden" name="mentor-{id}" id="mentor-{id}" field-id="{id}">
                                <input type="text" name="_mentor-{id}" id="_mentor-{id}" field-id="{id}" class="form-control formMentor" onfocus="mentorNameFocus()"  onblur="mentorNameBlur()" readonly>
                            </div>
                            <div class="d-flex align-items-end" style="width:fit-content;">
                                <a class="btn btn-outline-danger" onclick="removeRow({id})">X</a>
                            </div>
                        </div>`;

    var roster = {
        lastSelectedField: false,
        mentorListClicked: false,
        showingPositions: [
            @foreach($rosterGroups as $rg)
                {{$rg[0]->position->id}},
            @endforeach
        ],
        id: {{++$i}},
        nextId: function(){
            return this.id++;
        }
    };

    document.addEventListener("DOMContentLoaded", function () {
        resetList();
        resetMentorList();

        //event-position-selector" position-id="{{ $pos->id }}"
        roster.showingPositions.forEach((id) => {
            document.querySelector(`.event-position-selector[position-id="${id}"] > div`).classList.remove('border-danger');
            document.querySelector(`.event-position-selector[position-id="${id}"] > div`).classList.add('border-success');
        });
        
        document.addEventListener("click", function(e){
            const target = e.target.closest(".mentorItem");

            if(target){
                roster.mentorListClicked = true;
            } else if (e.target.closest('.formMentor')) {
                roster.mentorListClicked = true;
            } else {
                roster.mentorListClicked = false;
                document.getElementById('mentorList').classList.add('d-none');
            }
        });

        document.querySelectorAll('.time-init').forEach((el) => {
            el.flatpickr({ disableMobile: true, enableTime: true, noCalendar: true, dateFormat: "H:i\z", time_24hr: true});
        });
    });

    function removeRow(id){
        document.querySelector(`.controllerPositionGroup[field-id="${id}"]`).remove();
    }

    function addAdditionalController(positionId){
        const pos = getPosition(positionId);
        const rosterId = roster.nextId();

        var values = [];

        for(var i=0;i < rosterId;i++){
            const elHidden = document.getElementById(`mentor-${i}`);
            const el = document.getElementById(`_mentor-${i}`);
            const elStart = document.getElementById(`start-${i}`);
            const elEnd = document.getElementById(`end-${i}`);
            const elControllerHidden = document.getElementById(`controller-${i}`);
            const elController = document.getElementById(`_controller-${i}`);

            if(elHidden){

                const value = {
                    textValue: el.value,
                    realValue: elHidden.value,
                    startValue: elStart.value,
                    endValue: elEnd.value,
                    controllerText: elController.value,
                    controllerValue: elControllerHidden.value,
                }

                values[i] = value;
            }
        }

        if(positionId == '/1'){
            document.querySelector(`.event-position[position-id="${positionId}"]`).innerHTML += positionAddHTML
                .replaceAll('{callsign}', pos.callsign)
                .replaceAll('{code}', pos.code)
                .replaceAll('{posId}', pos.id)
                .replaceAll('{id}', rosterId)
                .replaceAll('onfocus="mentorNameFocus()"  onblur="mentorNameBlur()" readonly', 'disabled');
        } else {
            document.querySelector(`.event-position[position-id="${positionId}"]`).innerHTML += positionAddHTML
            .replaceAll('{callsign}', pos.callsign)
            .replaceAll('{code}', pos.code)
            .replaceAll('{posId}', pos.id)
            .replaceAll('{id}', rosterId);
        }

        


        for(var i=0;i < rosterId;i++){
            const elHidden = document.getElementById(`mentor-${i}`);
            const el = document.getElementById(`_mentor-${i}`);
            const elStart = document.getElementById(`start-${i}`);
            const elEnd = document.getElementById(`end-${i}`);
            const elControllerHidden = document.getElementById(`controller-${i}`);
            const elController = document.getElementById(`_controller-${i}`);

            if(values[i]){
                elHidden.value = values[i].realValue;
                el.value = values[i].textValue;
                elStart.value = values[i].startValue;
                elEnd.value = values[i].endValue;
                elControllerHidden.value = values[i].controllerValue;
                elController.value = values[i].controllerText;

                document.getElementById(`start-${i}`).flatpickr({ disableMobile: true, enableTime: true, noCalendar: true, dateFormat: "H:i\z", time_24hr: true});
                document.getElementById(`end-${i}`).flatpickr({ disableMobile: true, enableTime: true, noCalendar: true, dateFormat: "H:i\z", time_24hr: true});
            }
        }

        document.getElementById(`start-${rosterId}`).flatpickr({ disableMobile: true, enableTime: true, noCalendar: true, dateFormat: "H:i\z", time_24hr: true});
        document.getElementById(`end-${rosterId}`).flatpickr({ disableMobile: true, enableTime: true, noCalendar: true, dateFormat: "H:i\z", time_24hr: true});
    }

    function submit(publish){
        const form = document.getElementById("rosterForm");

        if(publish){
            form.setAttribute('action', formPublishURL);
        } else {
            form.setAttribute('action', formSubmitURL);
        }

        form.submit();
    }

    function togglePosition(id){
        console.log(roster);
        if(roster.showingPositions.indexOf(id) > -1){
            document.querySelector(`.event-position[position-id="${id}"]`).remove();
            removeElementAtIndex(roster.showingPositions, roster.showingPositions.indexOf(id));

            document.querySelector(`.event-position-selector[position-id="${id}"] > div`).classList.remove('border-success');
            document.querySelector(`.event-position-selector[position-id="${id}"] > div`).classList.add('border-danger');
        } else {
            const pos = getPosition(id);
            const rosterId = roster.nextId();

            var values = [];

            for(var i=0;i < rosterId;i++){
                const elHidden = document.getElementById(`mentor-${i}`);
                const el = document.getElementById(`_mentor-${i}`);
                const elStart = document.getElementById(`start-${i}`);
                const elEnd = document.getElementById(`end-${i}`);
                const elControllerHidden = document.getElementById(`controller-${i}`);
                const elController = document.getElementById(`_controller-${i}`);

                if(elHidden){

                    console.log(el.value);

                    const value = {
                        textValue: el.value,
                        realValue: elHidden.value,
                        startValue: elStart.value,
                        endValue: elEnd.value,
                        controllerText: elController.value,
                        controllerValue: elControllerHidden.value,
                    }

                    values[i] = value;
                }
            }

            console.log(values);

            document.getElementById("eventPositions").innerHTML += positionHTML
                .replaceAll('{callsign}', pos.callsign)
                .replaceAll('{code}', pos.code)
                .replaceAll('{posId}', pos.id)
                .replaceAll('{id}', rosterId);


            for(var i=0;i < rosterId;i++){
                const elHidden = document.getElementById(`mentor-${i}`);
                const el = document.getElementById(`_mentor-${i}`);
                const elStart = document.getElementById(`start-${i}`);
                const elEnd = document.getElementById(`end-${i}`);
                const elControllerHidden = document.getElementById(`controller-${i}`);
                const elController = document.getElementById(`_controller-${i}`);

                if(values[i]){
                    elHidden.value = values[i].realValue;
                    el.value = values[i].textValue;
                    elStart.value = values[i].startValue;
                    elEnd.value = values[i].endValue;
                    elControllerHidden.value = values[i].controllerValue;
                    elController.value = values[i].controllerText;

                    document.getElementById(`start-${i}`).flatpickr({ disableMobile: true, enableTime: true, noCalendar: true, dateFormat: "H:i\z", time_24hr: true});
                    document.getElementById(`end-${i}`).flatpickr({ disableMobile: true, enableTime: true, noCalendar: true, dateFormat: "H:i\z", time_24hr: true});
                }
            }

            document.getElementById(`start-${rosterId}`).flatpickr({ disableMobile: true, enableTime: true, noCalendar: true, dateFormat: "H:i\z", time_24hr: true});
            document.getElementById(`end-${rosterId}`).flatpickr({ disableMobile: true, enableTime: true, noCalendar: true, dateFormat: "H:i\z", time_24hr: true});

            roster.showingPositions.push(id);

            document.querySelector(`.event-position-selector[position-id="${id}"] > div`).classList.add('border-success');
            document.querySelector(`.event-position-selector[position-id="${id}"] > div`).classList.remove('border-danger');
        }
        console.log(roster.showingPositions);
    }

    function removeElementAtIndex(arr, i) {
        Array.prototype.splice.call(arr, i, 1);
    }

    function resetList(){
        const userList = document.querySelector('#userList > ul');

        var html = '<li class="list-group-item list-group-item-action px-1 userListItem" value="0" onclick="selectController(0)">None</li>';

        avail.forEach((a) => {
            if(a.available){
                html += `<li class="list-group-item list-group-item-action px-1 userListItem" value="${ a.user.id }" onclick="selectController(${ a.user.id })">
                            ${ a.user.first_name } ${ a.user.last_name } (${ a.user.id })
                        </li>`;
            }
        });

        userList.innerHTML = html;
    }

    function resetMentorList(){
        const mentorList = document.querySelector('#mentorList > div');

        var html = '';

        avail.forEach((a) => {
            if(a.available){
                html += `<button class="list-group-item list-group-item-action px-1 mentorItem" value="${ a.user.id }" onclick="selectMentor(${ a.user.id })">${ a.user.first_name } ${ a.user.last_name } (${ a.user.id })</button>`;
            }
        });

        mentorList.innerHTML = html;
    }

    function getUserName(id){
        var a = false;

        a = avail.find((el) => el.user.id == id);

        return `${a.user.first_name} ${a.user.last_name}`;
    }

    function getPosition(id){
        var pos = false;

        if(id == '/1'){
            return {
                id: '/1',
                code: '',
                callsign: 'Backup',
                frequency: '',
            }
        }



        Object.keys(positions).forEach(function(key,index) {
            if(!pos)
                pos = positions[key].find((el) => el.id == id);
        });

        //pos = positions.find((el) => el.id == id);

        return pos;
    }

    function selectMentor(id){
        const fieldId = roster.lastSelectedField.getAttribute('field-id');
        
        const hiddenField = document.getElementById(`mentor-${fieldId}`);

        console.log(`${hiddenField.value.trim().length}`);

        const currentMentors = (hiddenField.value.trim().length>0)?hiddenField.value.split(','):[];

        console.log(currentMentors);

        if(currentMentors.indexOf(id.toString()) > -1){

            removeElementAtIndex(currentMentors, currentMentors.indexOf(id));
            document.querySelector(`#mentorList button[value="${id}"]`).classList.remove('active');

        } else {

            currentMentors.push(id.toString());
            document.querySelector(`#mentorList button[value="${id}"]`).classList.add('active');

        }

        console.log(currentMentors);

        hiddenField.value = currentMentors.join(',');

        var mentorsRaw = '';

        currentMentors.forEach((id) => {
            mentorsRaw += getUserName(id) + ', ';
        });

        roster.lastSelectedField.value = mentorsRaw.substring(0, mentorsRaw.length - 2);
    }

    function mentorNameFocus(){
        const target = document.activeElement;
        roster.lastSelectedField = target;

        
        const fieldId = roster.lastSelectedField.getAttribute('field-id');
        const mentorsField = document.getElementById(`mentor-${fieldId}`);

        const currentMentors = (mentorsField.value.trim().length>0)?mentorsField.value.split(','):[];

        const allMentors = document.querySelectorAll(`.mentorItem`);

        allMentors.forEach((el) => {
            if(currentMentors.indexOf(el.value)>-1){
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });


        const rect = getOffset(target);

        mentorDOM.style.top = rect.bottom + 'px';
        mentorDOM.style.left = rect.left + 'px';

        mentorDOM.style.width = rect.width + 'px';

        mentorDOM.classList.remove('d-none');

    }

    function mentorNameBlur(){
        setTimeout(function(){
            if(!roster.mentorListClicked)
                mentorDOM.classList.add('d-none');
        }, 100);
    }

    function selectController(id){
        console.log('controller selected');
        const fieldId = roster.lastSelectedField.getAttribute('field-id');

        const hiddenField = document.getElementById(`controller-${fieldId}`);

        if(id == 0){
            hiddenField.value = '';
            roster.lastSelectedField.value = '';
            return;
        }

        hiddenField.value = id;
        roster.lastSelectedField.value = getUserName(id);
    }

    function controllerNameFocus(){
        const target = document.activeElement;
        roster.lastSelectedField = target;

        const rect = getOffset(target);

        listDOM.style.top = rect.bottom + 'px';
        listDOM.style.left = rect.left + 'px';

        listDOM.style.width = rect.width + 'px';

        listDOM.classList.remove('d-none');
    }

    function controllerNameBlur(){
        setTimeout(function(){
            listDOM.classList.add('d-none');
        }, 200);
    }

    function getOffset(el) {
        const rect = el.getBoundingClientRect();
        return {
            left: rect.left + window.scrollX,
            top: rect.top + window.scrollY,
            bottom: rect.top + window.scrollY + rect.height,
            width: rect.width
        };
    }
</script>

@endsection