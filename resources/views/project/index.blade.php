@extends('layouts.wrapper')

@section('scripts')
{!! Html::script('js/project.js') !!}
@stop

@section('nav/projects/class')
active
@stop

@section('contentTitle')
{!! Html::toolbar('project', ['project' => $project]) !!}
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
], $active, 'activity') !!}

<div class="inside-tabs">
    {!! Html::startBox() !!}

    @if (isset($issues))
    @include('project/index/issues')
    @else
    @include('project/index/activity')
    @endif

    {!! Html::endBox() !!}
</div>

@stop
