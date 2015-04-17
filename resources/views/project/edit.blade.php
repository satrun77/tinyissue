@extends('layouts.wrapper')

@section('scripts')
    {!! Html::script(elixir('js/tiny_project.js')) !!}
@stop

@section('nav/projects/class')
active
@stop

@section('headingTitle')
    @lang('tinyissue.update') {!! link_to($project->to(), $project->name) !!}
@stop

@section('headingSubTitle')
    @lang('tinyissue.update_project_description')
@stop

@section('headingLink')
    {!! link_to($project->to('issue/new'), trans('tinyissue.new_issue')) !!}
@stop

@section('content')
{!! Form::form($form, ['action'=>'','secure'=>null, 'id' =>'submit-project']) !!}
@stop
