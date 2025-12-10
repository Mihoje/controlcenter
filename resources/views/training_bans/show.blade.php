@extends('layouts.app')

@section('title', ($inactive ? 'Inactive ' : '') . 'Training Bans')

@section('title-flex')
<div class="d-flex align-items-center">
    @can('issueTrainingBan', \App\Models\Training::class)
    <a href="{{ route('trainingban.create') }}" class="btn btn-success me-2">Issue training ban</a>
    @endcan
    <a href="{{ route($inactive ? 'trainingban.show' : 'trainingban.show.inactive') }}" class="btn btn-primary">View {{ $inactive ? 'active' : 'inactive' }} bans</a>
</div>
@endsection

@section('header')
    @vite(['resources/sass/bootstrap-table.scss', 'resources/js/bootstrap-table.js'])
@endsection

@section('content')
<div class="card shadow mb-4">
    <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 fw-bold text-white">
            {{ $inactive ? 'Inactive' : 'Active' }} Training Bans
        </h6>
    </div>
    <div class="card-body {{ $bans->count() == 0 ? '' : 'p-0' }}">

        @if($bans->count() == 0)
            <p class="mb-0">No training bans recorded</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-leftpadded mb-0" width="100%" cellspacing="0"
                    data-page-size="25"
                    data-toggle="table"
                    data-pagination="true"
                    data-filter-control="true"
                    data-sort-reset="true">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Expires</th>
                            <th>Reason</th>
                            <th>Banned on</th>
                            <th>Banned by</th>
                            @if(!$inactive)
                            <th>Commands</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bans as $ban)
                            <tr>
                                <td style="vertical-align:middle">
                                    <a href="{{ route('user.show', $ban->user->id) }}">{{ $ban->user->name }} ({{$ban->user->id}})</a>
                                </td>
                                <td style="vertical-align:middle">
                                    {{ \Carbon\Carbon::parse($ban->expires_on)->toFormattedDateString() }}
                                </td>
                                <td style="vertical-align:middle">
                                    {{ $ban->reason }}
                                </td>
                                <td style="vertical-align:middle">
                                    {{ \Carbon\Carbon::parse($ban->created_at)->toFormattedDateString() }}
                                </td>
                                <td style="vertical-align:middle">
                                    <a href="{{ route('user.show', $ban->issuer->id) }}">{{ $ban->issuer->name }} ({{$ban->issuer->id}})</a>
                                </td>
                                @if(!$inactive)
                                <td style="vertical-align:middle">
                                    @can('revoke', $ban)
                                        <a href="{{ route('trainingban.revoke', $ban->id) }}" class="revoke-btn btn btn-success">Revoke ban</a>
                                    @endcan
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>             
@endsection

@section('js')
<script>
document.addEventListener('click', function(e){
    if(e.target && e.target.classList.contains('revoke-btn')){
        e.preventDefault();
        e.stopPropagation();

        const redirect_url = e.target.getAttribute('href');

        var success = confirm('Are you sure you want to revoke this ban?');

        if(success){
            window.location.href = redirect_url;
        }
    }
});
</script>
@endsection