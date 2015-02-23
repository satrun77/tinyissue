@extends('layouts.wrapper')

@section('nav/projects/class')
active
@stop

@section('contentTitle')
{!! Html::toolbar('edit_issue', ['project' => $project]) !!}
@stop

@section('content')
{!! Form::form($form, ['action'=>'','secure'=>null]) !!}
@stop
