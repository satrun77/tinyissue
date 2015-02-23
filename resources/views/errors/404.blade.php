@extends('layouts.error')

@section('header1')
    @if($messages = ['We need a map.', 'I think we\'re lost.', 'We took a wrong turn.'])
    @endif
    {{ $messages[mt_rand(0, 2)] }}
@stop

@section('header2')
    Server Error: 404 (Not Found)
@stop

@section('message')
    We couldn't find the page you requested on our servers. We're really sorry
    about that. It's our fault, not yours. We'll work hard to get this page
    back online as soon as possible.
@stop
