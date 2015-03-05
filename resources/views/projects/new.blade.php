@extends('layouts.wrapper')

@section('scripts')
{!! Html::script('js/project-new.js') !!}
@stop

@section('nav/projects/class')
active
@stop

@section('scripts')
{!! Html::style('js/project-new.js') !!}
@stop

@section('contentTitle')
    {!! Html::heading('title', ['title' => 'create_a_new_project', 'subTitle' => 'create_a_new_project_description']) !!}
@stop

@section('content')
 {!! Form::form($form, ['action'=>'','secure'=>null, 'id' =>'submit-project']) !!}
@stop


        