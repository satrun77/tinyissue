@extends('layouts.wrapper')

@section('nav/admin/class')
active
@stop

@section('headingTitle')
    @lang('tinyissue.update_tag')
@stop

@section('headingSubTitle')
    @lang('tinyissue.update_tag_description')
@stop

@section('content')
  {!! Form::form($form, ['action'=>'','secure'=>null]) !!}
@stop
