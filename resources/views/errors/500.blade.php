@extends('layouts.error')

@section('header1')
@if($messages = ['Ouch.', 'Oh no!', 'Whoops!'])
@endif
{{ $messages[mt_rand(0, 2)] }}
@stop

@section('header2')
    Server Error: 500 (Internal Server Error)
@stop

@section('message')
        Something went wrong on our servers while we were processing your request.
        We're really sorry about this, and will work hard to get this resolved as
        soon as possible.
@stop
