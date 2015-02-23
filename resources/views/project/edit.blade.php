@extends('layouts.wrapper')

@section('scripts')
{!! Html::script('js/project.js') !!}
@stop

@section('nav/projects/class')
active
@stop

@section('contentTitle')
    {!! Html::toolbar('edit_project', ['project' => $project]) !!}
@stop

@section('content')
{!! Form::form($form, ['action'=>'','secure'=>null, 'id' =>'submit-project']) !!}
@stop
