@extends('layouts.wrapper')

@section('nav/dashboard/class')
active
@stop

@section('contentTitle')
    {!! Html::heading('title', ['title' => 'update_user', 'subTitle' => 'update_user_description']) !!}
@stop

@section('content')
  {!! Form::form($form, ['action'=>'','secure'=>null]) !!}
@stop
