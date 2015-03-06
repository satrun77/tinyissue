@extends('layouts.wrapper')

@section('nav/dashboard/class')
active
@stop

@section('headingTitle')
    @lang('tinyissue.update_user')
@stop

@section('headingSubTitle')
    @lang('tinyissue.update_user_description')
@stop

@section('content')
  {!! Form::form($form, ['action'=>'','secure'=>null]) !!}
@stop
