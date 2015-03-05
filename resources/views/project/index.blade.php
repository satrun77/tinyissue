@extends('layouts.wrapper')

@section('styles')
    {!! Html::style('css/tokenfield.css') !!}
    @parent
@stop

@section('scripts')
    {!! Html::script('js/jquery.tokenfield.js') !!}
    {!! Html::script('js/project.js') !!}
@stop

@section('nav/projects/class')
    active
@stop

@section('contentTitle')
    {!! Html::heading('project', ['project' => $project]) !!}
@stop

@section('content')

{!! Html::tab([
    [
        'url' => $project->to(),
        'page' => 'activity'
    ],
    [
        'url' => $project->to('issues'),
        'page' => 'open_issue',
        'count' => $open_issues_count,
    ],
    [
        'url' => $project->to('issues') . '/0',
        'page' => 'closed_issue',
        'count' => $closed_issues_count,
    ],
    [
        'url' => $project->to('assigned'),
        'page' => 'issue_assigned_to_you',
        'count' => $assigned_issues_count,
    ],
    [
    'url' => $project->to('notes'),
    'page' => 'notes',
    'count' => $notes_count,
    ],
], $active, 'activity') !!}

    <div class="inside-tabs {{ $active }}">

        @if (isset($issues))
            {!! Html::startBox('blue-box gray-box toolbar') !!}
            {!! Form::form($filterForm, ['action'=>'', 'method'=>'GET']) !!}
            {!! Html::endBox() !!}
        @endif

        {!! Html::startBox() !!}

        @if (isset($issues))
            @include('project/index/issues')
        @elseif(isset($notes))
            @include('project/index/notes')
        @else
            @include('project/index/activity')
        @endif

        {!! Html::endBox() !!}
    </div>

@stop
