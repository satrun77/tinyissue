@extends('layouts.wrapper')

@section('nav/admin/class')
active
@stop

@section('headingTitle')
    @lang('tinyissue.add_tag')
@stop

@section('headingSubTitle')
    @lang('tinyissue.add_new_tag')
@stop

@section('content')

 {!! Form::form($form, ['action'=>'','secure'=>null]) !!}

@stop
