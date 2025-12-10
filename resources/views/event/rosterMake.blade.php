@extends('layouts.app')

@section('title', 'Create roster')
@section('content')
<script
  src="https://code.jquery.com/jquery-3.6.1.js"
  integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI="
  crossorigin="anonymous"></script>
  <script src="https://cdn.ckeditor.com/4.19.1/standard/ckeditor.js"></script>

  <div class="alert alert-danger d-none" id="error-message"></div>
<div class="row">
    <div class="col col-lg-5">

        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-white">
                    Event details
                </h6> 
            </div>
            <div class="card-body">
                

                    <div class="form-group">
                        <label for="event_name">Event name</label>
                        <input type="text" id="event_name" class="form-control" name="event_name" required readonly value="{{$event->name}} | {{Carbon\Carbon::parse($event->start)->format("d.m.Y H:i")}} - {{Carbon\Carbon::parse($event->end)->format("H:i")}}">

                       
                    </div>
                    <input type="hidden" id="globalStart" value="{{Carbon\Carbon::parse($event->start)->format('H:i')}}">
                    <input type="hidden" id="globalEnd" value="{{Carbon\Carbon::parse($event->end)->format('H:i')}}">
                      
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td>User</td>
                                    <td>Availability</td>
                                    <td>Time</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($avlb as $av)
                                <tr>
                                    <td>{{(App\Models\User::where('id', $av->user_id)->first())->name}} [{{$av->user_id}}] ({{(App\Models\User::where('id', $av->user_id)->first())->rating_short}}) @if($av->is_avlb_as_pilot) <i class="fas fa-plane text-info"></i> @endif</td>
                                    <td>@if($av->is_available)<i class="fas fa-check-double text-success"></i> @else <i class="fas fa-times text-danger"></i> @endif</td>
                                    <td>@if($av->is_available){{Carbon\Carbon::parse($av->start)->format('H:i')}} - {{Carbon\Carbon::parse($av->end)->format('H:i')}}@else / @endif</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    
                    
                   
                    <div>
                        <strong>Booked stations: </strong> 
                           <span> {{$ev_stations->implode('station_name', ", ")}} </span>
                        
                    </div>
                    <div>
                        <!--<form action="{{route("event.roster.save")}}" method="post" id="myform" name="myform" class="myform" enctype="multipart/form-data">
                            -->
                        @php $evs_arr = $ev_stations->pluck('station_name')->toArray(); $evs_arr[] = 'ADR_E_CTR'; $evs_arr[] = 'ADR_W_CTR'; @endphp
                        @php $i=0; $inner=1;@endphp
                        @foreach($stations as $station)
                        @if($inner%4==1) </div> @endif
                        @if($i%4==0) <div class="row"> @endif
                        <div class="col"><input type="checkbox" id="change{{$station->id}}" name="stationsCheck[]" value="{{$station->id}}" onchange="addPos({{$station->id}})" @if(in_array($station->callsign, $evs_arr)) checked @endif/>{{$station->callsign}}</div>
                        
                        @php $i++; $inner++; @endphp
                        @endforeach
                    </div>
                    
                  
 
                    
                   

            </div>
        </div>
    </div>
    <div class="col col-lg-7">

        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-white">
                    Roster workshop
                </h6> 
            </div>
            <div class="card-body">
                <form action="{{route("event.roster.save")}}" method="post" id="myform" name="myform" class="myform" enctype="multipart/form-data">
                    @csrf
                    
                    <input type="hidden" name="event_id" value={{$event->id}}>
                    <div class="d-none">
                        <!--<form action="{{route("event.roster.save")}}" method="post" id="myform" name="myform" class="myform" enctype="multipart/form-data">
                            -->
                        @php $evs_arr = $ev_stations->pluck('station_name')->toArray(); $evs_arr[] = 'ADR_E_CTR'; $evs_arr[] = 'ADR_W_CTR'; @endphp
                        @php $i=0; $inner=1;@endphp
                        @foreach($stations as $station)
                        @if($inner%4==1) </div> @endif
                        @if($i%4==0) <div class="row"> @endif
                        <div class="col"><input type="checkbox" hidden id="change2{{$station->id}}" name="stationsCheck[]" value="{{$station->id}}" onchange="addPos({{$station->id}})" @if(in_array($station->callsign, $evs_arr)) checked @endif/></div>
                        
                        @php $i++; $inner++; @endphp
                        @endforeach
                    </div>
                    @php
                    $user_arr = [];
                    foreach($avlb as $av){
                        $user_arr[$av->user_id] = (App\Models\User::where('id', $av->user_id)->first())->name;
                    }
                    $ev_stat = explode(", ", $ev_stations->implode("station_name", ", "));
                    if(!in_array("ADR_E_CTR", $ev_stat)) $ev_stat [] = "ADR_E_CTR";
                    if(!in_array("ADR_W_CTR", $ev_stat)) $ev_stat [] = "ADR_W_CTR";
                    @endphp
                    @foreach($stations as $station)
                    <input type="hidden" id="station{{$station->id}}Count" value="1">
                    <div class="border-bottom "  @if(!in_array($station->callsign, $ev_stat)) style="display: none;" @endif id="station{{$station->id}}Cont" >
                    <div class="row form-group " >
                        <input type="hidden" id="hidden_start" value='{{Carbon\Carbon::parse($event->start)->format("H:i")}}'>
                        <input type="hidden" id="hidden_end" value='{{Carbon\Carbon::parse($event->end)->format("H:i")}}'>
                        <div class="col">
                            <strong>{{$station->callsign}}</strong><span class="btn btn-success btn-sm ml-1" onclick="addTime({{$station->id}})">Add</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="stations">Controller</label><i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="top" title="Choose one or more controllers to fill this position."></i>
                                <select class="form-control selectCont" name="controllers{{$station->id}}1[]" id="controllers{{$station->id}}1[]" multiple >                               
                                     @foreach($avlb as $av)       
                                        <option value="{{$av->user_id}}">{{$user_arr[$av->user_id]}}</option>
                                    @endforeach
                                  </select>
                            </div>
                        </div>
                        
                        <div class="col">
                            
                            <label for="start_at{{$station->id}}1">Start (Zulu)</label>
                            <input id="start_at{{$station->id}}1" class="form-control start_time @error('start_at') is-invalid @enderror" type="time" name="start_at{{$station->id}}1" placeholder="{{trim(Carbon\Carbon::parse($event->start)->format("H:i"))}}" value="@if(old('start_at')){{trim(old('start_at'))}}@else{{trim(Carbon\Carbon::parse($event->start)->format("H:i"))}}@endif" required onChange="checkTime()">
                            @error('start_at')
                                <span class="text-danger">{{ $errors->first('start_at') }}</span>
                            @enderror
                        </div>
                        <div class="col">
                            <label for="end_at">End (Zulu)</label>
                            <input id="end_at{{$station->id}}1" class="form-control end_time @error('end_at') is-invalid @enderror" type="time" name="end_at{{$station->id}}1" placeholder="{{trim(Carbon\Carbon::parse($event->end)->format("H:i"))}}" value="@if(old('end_at')){{trim(old('end_at'))}}@else{{trim(Carbon\Carbon::parse($event->end)->format("H:i"))}}@endif" required onChange="checkTime()">
                            @error('end_at')
                                <span class="text-danger">{{ $errors->first('end_at') }}</span>
                            @enderror
                        </div>
                        <div class="col" >
                            <div class="form-group">
                                <label for="stations">Mentor</label><i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="top" title="Choose one or more controllers to mentor this position."></i>
                                <select class="form-control selectCont" name="mentors{{$station->id}}1[]" id="mentors{{$station->id}}1[]" multiple >                               
                                     @foreach($avlb as $av)       
                                        <option value="{{$av->user_id}}">{{$user_arr[$av->user_id]}}</option>
                                    @endforeach
                                  </select>
                            </div>
                        </div>
                       
                    </div>
                    <div id="station{{$station->id}}Contnovi">
                    </div>
                    </div>
                    @endforeach
                    <div class="border-bottom "   id="statioBackupCont" >
                        <div class="row form-group " >
                            <input type="hidden" id="hidden_start" value='{{Carbon\Carbon::parse($event->start)->format("H:i")}}'>
                            <input type="hidden" id="hidden_end" value='{{Carbon\Carbon::parse($event->end)->format("H:i")}}'>
                            <div class="col">
                                <strong>BACKUPS</strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="stations">Controller</label><i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="top" title="Choose one or more controllers to fill this position."></i>
                                    <select class="form-control selectCont" name="controllersBackup[]" id="controllersBackup[]" multiple >                               
                                         @foreach($avlb as $av)       
                                            <option value="{{$av->user_id}}">{{$user_arr[$av->user_id]}}</option>
                                        @endforeach
                                      </select>
                                </div>
                            </div>
                            
                            <div class="col">
                                
                                <label for="start_at{{$station->id}}1">Start (Zulu)</label>
                                <input id="start_at{{$station->id}}1" class="form-control start_time @error('start_at') is-invalid @enderror" type="time" name="start_atBackup" placeholder="{{trim(Carbon\Carbon::parse($event->start)->format("H:i"))}}" value="@if(old('start_at')){{trim(old('start_at'))}}@else{{trim(Carbon\Carbon::parse($event->start)->format("H:i"))}}@endif" required onChange="checkTime()">
                                @error('start_at')
                                    <span class="text-danger">{{ $errors->first('start_at') }}</span>
                                @enderror
                            </div>
                            <div class="col">
                                <label for="end_at">End (Zulu)</label>
                                <input id="end_at{{$station->id}}1" class="form-control end_time @error('end_at') is-invalid @enderror" type="time" name="end_atBackup" placeholder="{{trim(Carbon\Carbon::parse($event->end)->format("H:i"))}}" value="@if(old('end_at')){{trim(old('end_at'))}}@else{{trim(Carbon\Carbon::parse($event->end)->format("H:i"))}}@endif" required onChange="checkTime()">
                                @error('end_at')
                                    <span class="text-danger">{{ $errors->first('end_at') }}</span>
                                @enderror
                            </div>
                        
                           
                        </div>
                        
                        </div>
                        <div class="form-group">
                            <label for="msg"><strong>Message for controllers</strong></label>
                            <textarea class="" name="msg" id="msg" onchange="updateMsg()"></textarea>
                            <script>
                                    CKEDITOR.replace( 'msg' );
                            </script>
                        </div>
                   
                    
                  
 
                    
                    <input type="submit" class="btn btn-info mt-1"  value="Save roster" />
                    
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@section('js')
<script>



</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    function addPos(id){
        if(document.getElementById("change"+id).checked){
            document.getElementById('station'+id+'Cont').style.display = "block";
            $(".selectCont").select2({
            matcher: matchCustom
            });     
            document.getElementById("change2"+id).checked = true;   
        }else{
            document.getElementById('station'+id+'Cont').style.display = "none";
            document.getElementById("change2"+id).checked = false;  
        }
    }
    function addTime(id){
        count = parseInt(document.getElementById('station'+id+'Count').value);
        count++;
        console.log(count);
        document.getElementById('station'+id+'Count').value = count;
        start = document.getElementById('end_at'+id+(count-1)).value;
        end = document.getElementById('globalEnd').value;
        select = document.getElementById("controllers"+id+'1[]');
        let sel = document.createElement("select");
        addtxt = count==2?"":count-1;
        console.log('station'+id+'Contnovi'+addtxt);
        document.getElementById('station'+id+'Contnovi'+addtxt).innerHTML += `<div class="row border-top"><div class="col">
            <div class="form-group">
                                <label for="stations">Controller</label><i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="top" title="Choose one or more controllers to fill this position."></i>
                                <select class="form-control selectNew" name="controllers`+id+count+`[]" id="controllers`+id+count+`[]" multiple >                               
                                  </select>
                            </div>
            </div><div class="col">
                            
                            <label for="start_at">Start (Zulu)</label>
                            <input id="start_at`+id+count+`" class="form-control start_time @error('start_at') is-invalid @enderror" type="time" name="start_at`+id+count+`" placeholder="" value="`+start+`" required >
                            @error('start_at')
                                <span class="text-danger">{{ $errors->first('start_at') }}</span>
                            @enderror
                        </div>
                        <div class="col">
                            <label for="end_at">End (Zulu)</label>
                            <input id="end_at`+id+count+`" class="form-control end_time @error('end_at') is-invalid @enderror" type="time" name="end_at`+id+count+`" placeholder="" value="`+end+`" required >
                            @error('end_at')
                                <span class="text-danger">{{ $errors->first('end_at') }}</span>
                            @enderror
                        </div>
                        <div class="col">
            <div class="form-group" >
                                <label for="stations">Mentors</label><i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="top" title="Choose one or more mentors for this position."></i>
                                <select class="form-control selectNew" name="mentors`+id+count+`[]" id="mentors`+id+count+`[]" multiple >                               
                                  </select>
                            </div>
            </div>
                        </div><div id="station`+id+`Contnovi`+count+`"></div>`;
        newSelect = document.getElementById("controllers"+id+count+'[]');
        iter = select.options.length;
        for(i=0; i<iter; i++){
            var opt = document.createElement('option');
            opt.value = select.options[i].value;
            opt.innerHTML = select.options[i].innerHTML;
            newSelect.appendChild(opt);
        }
        newSelect = document.getElementById("mentors"+id+count+'[]');
        iter = select.options.length;
        for(i=0; i<iter; i++){
            var opt = document.createElement('option');
            opt.value = select.options[i].value;
            opt.innerHTML = select.options[i].innerHTML;
            newSelect.appendChild(opt);
        }
       $(".selectNew").select2({
  matcher: matchCustom
});            
    }

</script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>

    </script>
<script>
  function titleCase(str) {
  // Step 1. Lowercase the string
  str = str.toLowerCase();
  // str = "I'm a little tea pot".toLowerCase();
  // str = "i'm a little tea pot";
  
  // Step 2. Split the string into an array of strings
  str = str.split(' ');
  // str = "i'm a little tea pot".split(' ');
  // str = ["i'm", "a", "little", "tea", "pot"];
  
  // Step 3. Create the FOR loop
  for (var i = 0; i < str.length; i++) {
    str[i] = str[i].charAt(0).toUpperCase() + str[i].slice(1); 
  /* Here str.length = 5
    1st iteration: str[0] = str[0].charAt(0).toUpperCase() + str[0].slice(1);
                   str[0] = "i'm".charAt(0).toUpperCase()  + "i'm".slice(1);
                   str[0] = "I"                            + "'m";
                   str[0] = "I'm";
    2nd iteration: str[1] = str[1].charAt(0).toUpperCase() + str[1].slice(1);
                   str[1] = "a".charAt(0).toUpperCase()    + "a".slice(1);
                   str[1] = "A"                            + "";
                   str[1] = "A";
    3rd iteration: str[2] = str[2].charAt(0).toUpperCase()   + str[2].slice(1);
                   str[2] = "little".charAt(0).toUpperCase() + "little".slice(1);
                   str[2] = "L"                              + "ittle";
                   str[2] = "Little";
    4th iteration: str[3] = str[3].charAt(0).toUpperCase() + str[3].slice(1);
                   str[3] = "tea".charAt(0).toUpperCase()  + "tea".slice(1);
                   str[3] = "T"                            + "ea";
                   str[3] = "Tea";
    5th iteration: str[4] = str[4].charAt(0).toUpperCase() + str[4].slice(1);
                   str[4] = "pot".charAt(0).toUpperCase() + "pot".slice(1);
                   str[4] = "P"                           + "ot";
                   str[4] = "Pot";                                                         
    End of the FOR Loop*/
  }
  
  // Step 4. Return the output
  return str.join(' '); // ["I'm", "A", "Little", "Tea", "Pot"].join(' ') => "I'm A Little Tea Pot"
}
function matchCustom(params, data) {
    // If there are no search terms, return all of the data
    if ($.trim(params.term) === '') {
      return data;
    }

    // Do not display the item if there is no 'text' property
    if (typeof data.text === 'undefined') {
      return null;
    }
    data.text = titleCase(data.text);
    // `params.term` should be the term that is used for searching
    // `data.text` is the text that is displayed for the data object
    if (data.text.indexOf(params.term) > -1) {
      var modifiedData = $.extend({}, data, true);
      modifiedData.text += ' (matched)';

      // You can return modified objects from here
      // This includes matching the `children` how you want in nested data sets
      return modifiedData;
    }

    // Return `null` if the term should not be displayed
    return null;
}

$(".selectCont").select2({
  matcher: matchCustom
});
</script>
<script>
    //Activate bootstrap tooltips
    $(document).ready(function() {

        var defaultDate = "{{ old('date') }}"
        $(".datepicker").flatpickr({ disableMobile: true, minDate: "{!! date('Y-m-d') !!}", dateFormat: "d/m/Y", defaultDate: defaultDate, locale: {firstDayOfWeek: 1 } });

        $('.flatpickr-input:visible').on('focus', function () {
            $(this).blur();
        });
        $('.flatpickr-input:visible').prop('readonly', false);

        // Zulu clock
        var currentdate = new Date(); 
        var datetime = ('0'+currentdate.getUTCHours()).substr(-2,2) + ":" + ('0'+currentdate.getUTCMinutes()).substr(-2,2);

        setInterval(function (){
            var currentdate = new Date(); 
            var datetime = ('0'+currentdate.getUTCHours()).substr(-2,2) + ":" + ('0'+currentdate.getUTCMinutes()).substr(-2,2);
            $('.zulu-clock').text(datetime + 'z');
        },1000);
    })

    change = (type) => {
        let name = document.getElementsByName(type.name);
        let checked = document.getElementById(type.id);

        if (checked.checked) {
            for(let i = 0; i < name.length; i++) {
                if(!name[i].checked) {
                    name[i].disabled = true;
                } else {
                    name[i].disabled = false;
                }
            }
        } else {
            for(let i = 0; i < name.length; i++) {
                name[i].disabled = false;
            }
        }
    }
</script>
<script>


</script>
@endsection


