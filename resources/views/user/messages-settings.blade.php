@extends('layouts.wrapper')

@section('nav/settings/class')
    active
@stop

@section('headingTitle')
    @lang('tinyissue.my_messages_settings')
@stop

@section('headingSubTitle')
    @lang('tinyissue.my_messages_settings_description')
@stop

@section('headingLink')
    <a href="{!! url('user/settings') !!}" class="list-issues-btn">
        @lang('tinyissue.settings')
    </a>
@stop

@section('content')
    {!! Form::form($form, ['action'=>'user/settings/messages','secure'=>null]) !!}
@stop
