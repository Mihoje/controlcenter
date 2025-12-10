@extends('layouts.app')

@section('title', 'Blocked users')

@section('header')
    @vite(['resources/sass/bootstrap-table.scss', 'resources/js/bootstrap-table.js'])
@endsection

@section('content')

<div class="row">
    <div class="col-xl-6 col-12 mb-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">List of blocked users</h6> 
            </div>        
            <div class="card-body p-0">
                <div class="list-group">

                    @if(count($blockedusers) == 0)
                        <div class="list-group-item list-group-item-action" class="blocked">
                            <span class="text-muted">No blocked users</spans>
                        </div>
                    @endif

                    @foreach($blockedusers as $blocked)
                        <a href="#" class="list-group-item list-group-item-action" blocked-id="{{$blocked->id}}" class="blocked">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">{{ $blocked->user->getNameAttribute() }} - {{ $blocked->user->id }}</h5>
                                <small class="text-body-secondary">{{ $blocked->created_at }} UTC</small>
                            </div>
                            <p class="mb-1">Reason: {{ $blocked->reason }}</p>
                            <small class="text-body-secondary d-block pb-3">Issued by: {{ $blocked->issuer->getNameAttribute() }} - {{ $blocked->issuer->id }}</small>
                            <button class="btn btn-danger" onclick="deleteBlock({{$blocked->id}})">Unblock</button>
                        </a>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-12 mb-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">Block user</h6> 
            </div>        
            <div class="card-body">
                <form action="{{ route('blocked.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="user" class="form-label">User</label>
                        <input 
                            id="user"
                            class="form-control"
                            type="text"
                            name="user"
                            list="userList">

                        <datalist id="userList">
                            @foreach($users as $user)
                                @browser('isFirefox')
                                    <option>{{ $user->id }}</option>
                                @else
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endbrowser
                            @endforeach
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason</label>
                        <input type="test" class="form-control" name="reason" id="reason">
                    </div>
                    <button type="submit" class="btn btn-success">Block user</button>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function deleteBlock(e){
        var c = confirm('Are you sure you want to unblock the user?');

        if(c){
            window.location = `{{route('blocked.delete', ['id'=>-1])}}`.replace('-1', e);
        }

    };
</script>

@endsection