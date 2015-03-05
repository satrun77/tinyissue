@extends('layouts.wrapper')

@section('nav/projects/class')
active
@stop

@section('contentTitle')
    {!! Html::heading('title', ['title' => 'projects', 'subTitle' => 'projects_description']) !!}
@stop

@section('content')

{!! Html::tab([
    [
        'url' => URL::to('projects'),
        'page' => 'active',
        'count' => $active_count,
    ],
    [
        'url' => URL::to('projects') . '/0',
        'page' => 'archived',
        'count' => $archived_count
    ],
], $active, 'project') !!}

<div class="inside-tabs">
{!! Html::startBox() !!}

<ul class="projects">
    @foreach($content_projects as $project)
    <li>
        <a href="{{ $project->to() }}">{{ $project->name }}</a><br />

        @if ($project->openIssuesCount > 1)
        {{ $project->openIssuesCount }} @lang('tinyissue.open_issues')
        @else
        1  @lang('tinyissue.open_issue')
        @endif
    </li>
    @endforeach

    @if(count($content_projects) == 0)
    <li>@lang('tinyissue.you_do_not_have_any_projects') <a href="{{ URL::to('projects/new') }}">@lang('tinyissue.create_project')</a></li>
    @endif
</ul>
{!! Html::endBox() !!}
</div>

@stop
