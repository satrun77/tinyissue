@extends('layouts.wrapper')

@section('nav/issues/class')
active
@stop

@section('headingTitle')
    @lang('tinyissue.your_issues')
@stop

@section('headingSubTitle')
    @if (auth()->user()->isUser())
        @lang('tinyissue.your_created_issues_description')
    @else
        @lang('tinyissue.your_issues_description')
    @endif
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

@foreach($projects as $project)
@if (count($project->issues) > 0)
{!! Html::startBox('blue-box', [$project->name, $project->to()]) !!}

<ul class="issues">
    @foreach($project->issues as $issue)
    <li>
        <a href="{{ $issue->to() }}" class="comments">{{ $issue->countComments }}</a>

        <div class="issue-tags">
            @forelse($issue->tags as $tag)
                <label class="label" style="background: {{ $tag->bgcolor  or 'gray' }}">{!! Html::formatIssueTag($tag->fullname) !!}</label>
            @empty
            @endforelse
        </div>

        <a href="{{ $issue->to() }}" class="id">#{{ $issue->id }}</a>
        <div class="data">
            <a href="{{ $issue->to() }}">{{ $issue->title }}</a>
            <div class="info">
                @lang('tinyissue.created_by')
                <strong>{{ $issue->user->fullname }}</strong>
                {{ Html::age($issue->created_at) }}

                @if(!is_null($issue->updated_by))
                - @lang('tinyissue.updated_by') <strong>{{ $issue->updatedBy->fullname }}</strong>
                {{ Html::age($issue->updated_at) }}
                @endif

                @can('viewLockedQuote', $issue)
                - @lang('tinyissue.time_quote') <strong>{{ Html::duration($issue->time_quote) }}</strong>
                @endcan
            </div>
        </div>
    </li>
    @endforeach
</ul>
{!! Html::endBox() !!}
@endif
@endforeach

@stop
