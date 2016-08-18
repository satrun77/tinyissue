@extends('layouts.error')

@section('header1')
    @if($messages = ['No no no, not this page.', 'Did you take the wrong turn.', 'For your protection!'])
    @endif
    {{ $messages[mt_rand(0, 2)] }}
@stop

@section('header2')
    Server Error: 403 (Unauthorized)
@stop

@section('message')
    Sorry, but you are not allowed to access this page.
@stop
