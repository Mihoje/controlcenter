@extends('layouts.app')

@section('title', 'You are blocked')

@section('content-master')
    <div class="front-cover">
        <div class="content w-100">
            
            @if(Session::has('error') OR isset($error))
                <div class="alert alert-danger" role="alert">
                    <i class="fa fa-lg fa-exclamation-circle"></i> {!! Session::has('error') ? Session::pull("error") : $error !!}
                </div>
            @endif

            @if(Session::has('success') OR isset($success))
                <div class="alert alert-success" role="alert">
                    {!! Session::has('success') ? Session::pull("success") : $error !!}
                </div>
            @endif
            
            <div class="content-title w-100"><img src="{{ asset('images/control-tower.svg') }}"> {{ config('app.name') }}</div>
            <div class="content-description">
                You have been blocked from accessing the Control Center
                <small class="d-block">Blocked by: {{ $block->issuer->getNameAttribute() }} - {{ $block->issuer->id }}</small>
                <small class="d-block">Reason: {{ $block->reason }}</small>
            </div>
        </div>

        <div class="logo">
            <img src="{{ asset('images/logos/'.Config::get('app.logo')) }}">
            <a href="https://github.com/Vatsim-Scandinavia/controlcenter" target="_blank" class="version-front">Control Center v{{ config('app.version') }}</a>
        </div>
    </div>
@endsection