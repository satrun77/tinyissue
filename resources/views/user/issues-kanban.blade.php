@extends('layouts.wrapper')

@section('nav/issues/class')
active
@stop

@section('headingTitle')
    @lang('tinyissue.your_issues')
    @if ($project->name)
        - {{ $project->name }}
    @endif
@stop

@section('headingSubTitle')
    @lang('tinyissue.your_issues_description')
@stop

@section('headingLink')
    <a href="{!! url('user/issues/list') !!}" class="list-issues-btn">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </a>
    <a href="{!! url('user/issues/kanban') !!}" class="kanban-columns-btn">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </a>
@stop

@section('content')
    {!! Html::startBox('kanban-projects-list blue-box gray-box toolbar') !!}
    @lang('tinyissue.select_project')
    <div class="dropdown">
        <ul>
            @foreach ($projects as $aProject)
                <li>
                    <a href="{!! url('user/issues/kanban/' . $aProject->id) !!}" data-project-id="{{ $aProject->id }}">
                        {{ $aProject->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    <strong class="current">{{ $project->name }}</strong>
    {!! Html::endBox() !!}

    <div class="kanban-wrap">
        <ul class="kanban">
            @foreach($columns as $column)
                <li class="column column-{{ $column->id }}">
                    <div class="column-wrap">
                        <h2 class="heading">
                            {{ $column->name }}
                        </h2>
                        <div class="content" data-column="{{ $column->id }}">
                            @if ($issues->get($column->name))
                                @foreach($issues->get($column->name) as $issue)
                                    <div class="issue issue-{{ $issue->id }}"
                                         data-url="project/issue/{{ $issue->id }}/change_kanban_tag"
                                         data-column="{{ $column->id }}">
                                        <div class="summary">
                                            <a href="{{ $issue->to() }}" class="id">#{{ $issue->id }}</a>
                                            <span>{{ $issue->title }}</span>
                                        </div>

                                        <div class="info">
                                            @lang('tinyissue.created_by')
                                            <strong>{{ $issue->user->fullname }}</strong>
                                            {{ Html::age($issue->created_at) }}
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@stop
