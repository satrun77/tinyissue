@extends('layouts.wrapper')

@section('nav/settings/class')
active
@stop

@section('contentTitle')
    {!! Html::toolbar('title', ['title' => 'my_settings', 'subTitle' => 'my_settings_description']) !!}
@stop

@section('content')

  {!! Form::form($form, ['action'=>'user/settings','secure'=>null]) !!}

@stop
