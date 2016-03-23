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

    <nav id="kanban-projects-nav" class="kanban-nav">

        <ul>
            <li class="kanban-pill-tag">@lang('tinyissue.select_project'):</li>
            @foreach ($projects as $aProject)
                <li>
                    <a href="{!! url('user/issues/kanban/' . $aProject->id) !!}" data-project-id="{{ $aProject->id }}">
                        {{ $aProject->name }}
                    </a>
                </li>
            @endforeach
        </ul>
        </div>
    </nav>



    <div class="kanban-wrap">
        <ul class="kanban">
            @foreach($columns as $column)
                <li class="column column-{{ $column->id }}">
                    <div class="column-wrap">


                        <div class="arrowcaption" style="color:{{ $column->bgcolor or 'gray' }};">{{ $column->name }}</div>
                        <div class="arrow" style="border-color: #d3d3d3"></div>

                        <!--- Keep for the moment: Alternative display with colored border using the css inherit property
                            <div class="arrow">style="border-color:{{ $column->bgcolor or 'gray' }};"</div>
                        --->

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

                                        <div class="kanban-user">
                                            <img class="kanban-user-image" src="/images/layout/avatar_dummy.jpg">
                                        </div>

                                        <div class="info">
                                            <a class="info-user" href="{{ $issue->to() }}">{{ $issue->user->fullname }}</a>
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
