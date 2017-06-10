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
    <div class="kanban-wrap">
        <ul class="kanban">
            @foreach($columns as $column)
                <li class="column column-{{ $column->id }}">
                    <div class="column-wrap">

                        <div class="arrowcaption" style="color:{{ $column->bgcolor or 'gray' }};">{{ $column->name }}</div>
                        <div class="arrow"></div>

                        <div class="content @if($column->isReadOnly(auth()->user()))readonly @endif" data-column="{{ $column->id }}">
                            @if ($issues->get($column->name))
                                @foreach($issues->get($column->name) as $issue)
                                    <div class="issue issue-{{ $issue->id }}"
                                         data-url="project/issue/{{ $issue->id }}/change_kanban_tag"
                                         data-column="{{ $column->id }}"
                                         style="border-color:{{ $issue->typeColor or 'inherit' }};">
                                        <div class="summary">
                                            <a href="{{ $issue->to() }}" class="id">#{{ $issue->id }}</a>
                                            <span>{{ $issue->title }}</span>
                                        </div>

                                        <div class="kanban-user">
                                            <img class="kanban-user-image" src="{{ $issue->user->image }}">
                                        </div>

                                        <div class="info">
                                            @if ($issue->getResolutionTag())
                                            <label class="label" style="background: {{ $issue->getResolutionTag()->bgcolor or 'gray' }}">
                                                {!! Html::formatIssueTag($issue->getResolutionTag()->fullname) !!}
                                            </label>
                                            @endif
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
