@extends('layouts.wrapper')

@section('nav/projects/class')
active
@stop

@section('styles')
    {!! Html::style('css/tokenfield.css') !!}
    @parent
@stop

@section('scripts')
{!! Html::script('js/uploadify/jquery.uploadify.min.js') !!}
{!! Html::script('js/jquery.tokenfield.js') !!}
{!! Html::script('js/project.js') !!}
@stop

@section('contentTitle')
{!! Html::toolbar('add_issue', ['project' => $project]) !!}
@stop

@section('content')
{!! Form::form($form, ['action'=>'','secure'=>null]) !!}
@stop
