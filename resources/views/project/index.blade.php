@extends('layouts.wrapper')

@section('scripts')
    {!! Html::script(elixir('js/tiny_project.js')) !!}
@stop

@section('nav/projects/class')
    active
@stop

@section('headingTitle')
    {!! link_to($project->to(), $project->name) !!}
@stop

@section('headingSubTitle')
    @lang('tinyissue.project_overview')
@stop

@section('headingLink')
    @if (!Auth::guest())
        {!! link_to($project->to('issue/new'), trans('tinyissue.new_issue')) !!}
    @endif
@stop

@section('content')

{!! Html::tab($tabs, $active) !!}

    <div class="inside-tabs {{ $active }}">

        @if (isset($filterForm))
            {!! Html::startBox('blue-box gray-box toolbar') !!}
            {!! Form::form($filterForm, ['action'=>'', 'method'=>'GET']) !!}
            {!! Html::endBox() !!}
        @endif

        @if(isset($notes))
            @include('project/index/notes')
        @else
            {!! Html::startBox() !!}
            @if (isset($issues))
                @include('project/index/issues')
            @else
                @include('project/index/activity')
            @endif
            {!! Html::endBox() !!}
        @endif

    </div>

@stop
