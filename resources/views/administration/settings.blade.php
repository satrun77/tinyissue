@extends('layouts.wrapper')

@section('nav/admin/class')
active
@stop

@section('headingTitle')
    @lang('tinyissue.settings')
@stop

@section('headingSubTitle')
    @lang('tinyissue.manage_settings_description')
@stop

@section('content')

 {!! Form::form($form, ['action'=>'','secure'=>null]) !!}

@stop
