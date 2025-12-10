@extends('layouts.app')

@section('title', 'Issue training ban')
@section('content')

<div class="row">
    <div class="col-xl-6 col-md-12 mb-12">

        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-white">
                    Issue ban
                </h6>
            </div>
            <div class="card-body" id="training-selector">
                <form action="{{ route('trainingban.store') }}" method="post">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="student">Student</label>
                        <input
                            id="student"
                            class="form-control @error('student') is-invalid @enderror"
                            type="text"
                            name="user_id"
                            list="students"
                            value="{{ $prefillUser ? $prefillUser->id : old('student') }}"
                            required>

                        <datalist id="students">
                            @foreach($students as $student)
                                @browser('isFirefox')
                                    <option>{{ $student->id }}</option>
                                @else
                                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                                @endbrowser
                            @endforeach
                        </datalist>

                        @error('student')
                            <span class="text-danger">{{ $errors->first('student') }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="expire">Expires</label>
                        <input
                            id="expire"
                            class="datepicker form-control"
                            type="text"
                            name="expires">
                    </div>

                    <div class="mb-3">
                        <label for="reason">Reason</label>
                        <input
                            id="reason"
                            class="form-control"
                            type="text"
                            name="reason">
                    </div>

                    <button type="submit" class="btn btn-danger">Issue ban</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
@vite(['resources/js/flatpickr.js', 'resources/sass/flatpickr.scss'])
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelector('.datepicker').flatpickr({ disableMobile: true, minDate: "{!! date('Y-m-d') !!}", dateFormat: "d/m/Y", locale: {firstDayOfWeek: 1 } });
    })
</script>
@endsection
