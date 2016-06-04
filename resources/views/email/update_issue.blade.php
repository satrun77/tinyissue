@extends('email.base')

@section('header')
    @include('email/partials/message/header', ['changeByImage' => $changeByImage, 'changeByHeading' => $changeByHeading])
@stop

@section('body')
    @include('email/partials/message/row-title', ['project' => $project, 'issue' => $issue])
    @include('email/partials/message/row-changes', ['changes' => $changes])
@stop
