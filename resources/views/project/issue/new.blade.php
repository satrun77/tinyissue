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

@section('headingTitle')
    @lang('tinyissue.create_a_new_issue')
@stop

@section('headingSubTitle')
    @lang('tinyissue.create_a_new_issue_in')  {!! link_to($project->to(), $project->name) !!}
@stop

@section('content')
{!! Form::form($form, ['action'=>'','secure'=>null]) !!}
@stop
