@extends('layouts.wrapper')

@section('nav/projects/class')
    active
@stop

@section('scripts')
    {!! Html::script(elixir('js/tiny_project_issue.js')) !!}
@stop

@section('headingTitle')
    @lang('tinyissue.create_a_new_issue')
@stop

@section('headingSubTitle')
    @lang('tinyissue.create_a_new_issue_description')
@stop

@section('content')
    {!! Form::form($form, ['secure'=>null]) !!}
@stop
