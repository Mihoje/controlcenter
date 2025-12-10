@extends('layouts.app')

@section('title', 'Upload file')
@section('content')
<script
  src="https://code.jquery.com/jquery-3.6.1.js"
  integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI="
  crossorigin="anonymous"></script>



<div class="row">
    <div class="col col-lg-8">

        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-white">
                    Upoload file
                </h6> 
            </div>
            <div class="card-body">
                <form action="{{ route('filesMain.store') }}" method="post" enctype="multipart/form-data" >
                    @csrf
                    
 
                    <div class="form-group">
                        <label for="type">Type of document</label>
                        <select class="form-control" name="type" id="type" >
                            <option value="Training document">Training document</option>
                            <option value="Policy">Policy</option>
                            <option value="LOA">Letter of agreement</option>
                        </select>
                    </div>
                        <div class="form-group">
                            <input type="file" name="file" placeholder="Choose file" id="file" accept="application/pdf">
                                
                        </div>
                       
                        <!--<div class="form-group form-check">
                            <input value="true" type="checkbox" class="form-check-input" id="avlb" name="avlb" checked>
                            <label class="form-check-label" for="avlb">Show document after upload</label>
                        </div>-->
                           
                        <div class="">
                            <button type="submit" class="btn btn-primary" id="submit">Submit</button>
                        </div>
                     
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@section('js')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
   
    function matchStart(params, data) {
  // If there are no search terms, return all of the data
  if ($.trim(params.term) === '') {
    return data;
  }

  // Skip if there is no 'children' property
  if (typeof data.children === 'undefined') {
    return null;
  }

  // `data.children` contains the actual options that we are matching against
  var filteredChildren = [];
  $.each(data.children, function (idx, child) {
    if (child.text.toUpperCase().indexOf(params.term.toUpperCase()) == 0) {
      filteredChildren.push(child);
    }
  });

  // If we matched any of the timezone group's children, then set the matched children on the group
  // and return the group object
  if (filteredChildren.length) {
    var modifiedData = $.extend({}, data, true);
    modifiedData.children = filteredChildren;

    // You can return modified objects from here
    // This includes matching the `children` how you want in nested data sets
    return modifiedData;
  }

  // Return `null` if the term should not be displayed
  return null;
}

$(document).ready(function() {
    $("#stations").select2({
  matcher: matchStart
});
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


