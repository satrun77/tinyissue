@extends('layouts.wrapper')

@section('nav/dashboard/class')
active
@stop

@section('contentTitle')
    {!! Html::toolbar('title', ['title' => 'add_user', 'subTitle' => 'add_new_user']) !!}
@stop

@section('content')

 {!! Form::form($form, ['action'=>'','secure'=>null]) !!}

@stop
