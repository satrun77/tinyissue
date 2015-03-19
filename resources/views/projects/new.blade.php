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

@section('headingTitle')
    @lang('tinyissue.create_a_new_project')
@stop

@section('headingSubTitle')
    @lang('tinyissue.create_a_new_project_description')
@stop

@section('content')
 {!! Form::form($form, ['action'=>'','secure'=>null, 'id' =>'submit-project']) !!}
@stop
