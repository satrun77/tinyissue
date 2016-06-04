@extends('email.base')

@section('header')
    @include('email/partials/message/header', ['changeByImage' => $changeByImage, 'changeByHeading' => $changeByHeading])
@stop

@section('body')
    @include('email/partials/message/row-comment', ['comment' => $changes['note']])
    @include('email/partials/message/row-title', ['project' => $project])
    @include('email/partials/message/row-changes', ['changes' => $changes])
@stop
