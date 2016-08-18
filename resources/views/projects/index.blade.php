@extends('layouts.wrapper')

@section('nav/projects/class')
active
@stop

@section('headingTitle')
    @lang('tinyissue.projects')
@stop

@section('headingSubTitle')
    @if (!Auth::guest())
        @lang('tinyissue.projects_description')
    @else
        @lang('tinyissue.projects_description_guest')
    @endif
@stop

@section('content')

    {!! Html::tab([
        [
            'url' => URL::to('projects'),
            'page' => 'active',
            'prefix' => $active_count,
        ],
        [
            'url' => URL::to('projects') . '/0',
            'page' => 'archived',
            'prefix' => $archived_count
        ],
    ], $active) !!}

<div class="inside-tabs">
{!! Html::startBox() !!}

<ul class="projects">
    @foreach($content_projects as $project)
    <li>
        <a href="{{ $project->to() }}">{{ $project->name }}</a><br />

        <span>{{ $project->openIssuesCount }} @lang('tinyissue.open_issue' . ($project->openIssuesCount <= 1? '' : 's'))</span>
        @if(!Auth::guest())
            <span class="pull-right label label-{{ $project->getStatusClass() }}">
                 @lang('tinyissue.' . $project->getStatusAsName())
            </span>
        @endif
    </li>
    @endforeach

    @can('create', Tinyissue\Model\Project::class)
        @if(count($content_projects) == 0)
            <li>@lang('tinyissue.you_do_not_have_any_projects') <a href="{{ URL::to('projects/new') }}">@lang('tinyissue.create_project')</a></li>
        @endif
    @endcan
</ul>
{!! Html::endBox() !!}
</div>

@stop
