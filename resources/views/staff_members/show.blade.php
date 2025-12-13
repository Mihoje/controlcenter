@extends('layouts.app')

@section('title', 'Staff members')
@section('content')

<div class="row">
    <div class="col-12 col-xxl-6">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-white">
                    New staff member
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('staffmembers.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="user">User</label>
                        <input
                            id="user"
                            class="form-control @error('user') is-invalid @enderror"
                            type="text"
                            name="user"
                            list="users"
                            value="{{ old('user') }}"
                            required>

                        <datalist id="users">
                            @foreach($users as $user)
                                @browser('isFirefox')
                                    <option>{{ $user->id }}</option>
                                @else
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endbrowser
                            @endforeach
                        </datalist>
                        @error('user')
                            <span class="text-danger">{{ $errors->first('user') }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="title">Title</label>
                        <input
                            id="title"
                            class="form-control @error('title') is-invalid @enderror"
                            type="text"
                            name="title"
                            value="{{ old('title') }}"
                            required>
                        @error('title')
                            <span class="text-danger">{{ $errors->first('title') }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success">Add staff member</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xxl-6">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-white">
                    Staff members
                </h6>
            </div>
            <div class="card-body">
                <span class="text-muted fst-italic">Order by dragging</span>
                <div class="row" id="staffMembers">
                    @foreach ($staff as $member)
                        <div class="col-4 p-2" data-id="{{ $member->id }}">
                            <div class="card h-100">
                                <div class="card-body h-100" style="background-color:#CCC">
                                    <h5 class="card-title text-center">{{ $member->title }}</h5>
                                    <h6 class="card-subtitle mb-2 text-body-secondary text-center">{{ $member->user->id }}</h6>
                                    <p class="card-text text-center">{{ $member->user->name }}</p>
                                    <p class="text-center p-0 m-0"><a class="btn btn-danger btn-sm" href="{{ route('staffmembers.destroy', $member->id) }}">Remove</a></p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button class="btn btn-success" onClick="saveOrdering()">Save ordering</button>
            </div>
        </div>
    </div>
</div>



@endsection

@section('js')
<script>
    var sortable = null;
    window.addEventListener('load', function(){
        const el = document.getElementById('staffMembers');

        sortable = Sortable.create(
            el,
            {
                animation: 150,
                dataIdAttr: 'data-id'
            }
        );
    });

    function saveOrdering(){
        const saveArray = sortable.toArray();

        let obj = {
            '_token': "{{ csrf_token() }}"
        };

        let i = 0;

        saveArray.forEach(element => {
            obj[element] = i++;
        });

        const encoded = new URLSearchParams(obj).toString();
      
        var http = new XMLHttpRequest();
        var url = "{{ route('staffmembers.order') }}";
        
        http.open('POST', url, true);

        //Send the proper header information along with the request
        http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        http.onreadystatechange = function() {//Call a function when the state changes.
            if(http.readyState == 4 && http.status == 200) {
                const response = JSON.parse(http.responseText);
                console.log(response);

                if(!response.success){
                    notyf.error(response.reason ? response.reason : "Error");
                } else {
                    notyf.success("Successfully updated ordering");
                }
            }
        }
        http.send(encoded);
    }
</script>
@endsection


