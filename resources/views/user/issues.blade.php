@extends('layouts.wrapper')

@section('nav/issues/class')
active
@stop

@section('contentTitle')
{!! Html::heading('title', ['title' => 'your_issues', 'subTitle' => 'your_issues_description']) !!}
@stop

@section('content')

@foreach($projects as $project)
@if (count($project->issues) > 0)
{!! Html::startBox('blue-box', [$project->name, $project->to()]) !!}

<ul class="issues">
    @foreach($project->issues as $issue)
    <li>
        <a href="{{ $issue->to() }}" class="comments">{{ $issue->countComments }}</a>
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

                @if ($issue->time_quote > 0)
                    - @lang('tinyissue.time_quote') <strong>{{ Html::duration($issue->time_quote) }}</strong>
                @endif
            </div>
        </div>
    </li>
    @endforeach
</ul>
{!! Html::endBox() !!}
@endif
@endforeach

@stop
