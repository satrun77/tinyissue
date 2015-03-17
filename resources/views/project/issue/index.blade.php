@extends('layouts.wrapper')

@section('scripts')
    {!! Html::script('js/uploadify/jquery.uploadify.min.js') !!}
    {!! Html::script('js/project.js') !!}
@stop

@section('nav/projects/class')
active
@stop

@section('headingTitle')
    @if(\Auth::user()->permission('issue-modify'))
        {!! link_to($issue->to('edit'), $issue->title, ['class' => 'edit-issue']) !!}
    @else
        {!! link_to($issue->to(), $issue->title) !!}
    @endif
@stop

@section('headingSubTitle')
    @lang('tinyissue.on_project') {!! link_to($project->to(), $project->name) !!}
@stop

@section('headingLink')
    {!! link_to($project->to('issue/new'), trans('tinyissue.new_issue')) !!}
@stop

@section('content')
@if ($issue->time_quote > 0)
    <div class="issue-quote"><strong>@lang('tinyissue.time_quote'):</strong><span>{{ Html::duration($issue->time_quote) }}</span></div>
@endif

<div class="activity-tags">
@foreach($issue->tags()->with('parent')->get() as $tag)
    <label class="label" style="background: {{ $tag->bgcolor }}">{!! Html::formatIssueTag($tag->name, $tag->parent->name) !!}</label>
@endforeach
</div>

<span class="clearfix"></span>
<ul class="issue-discussion">
    <li>
        <div class="insides">
            <div class="topbar">
                <strong>{{ $issue->user->fullname }}</strong>
                @lang('tinyissue.opened_this_issue') {{ Html::date($issue->created_at) }}
            </div>

            <div class = "content">
                {!! Html::format($issue->body) !!}
            </div>

            <ul class = "attachments">
                @foreach($issue->attachments as $attachment)
                <li>
                    @if($attachment->isImage())
                        <a href="{{ $attachment->download() }}" title="{{ $attachment->filename }}"><img src="{{ $attachment->display() }}" alt="{{ $attachment->filename }}" class="image"/></a>
                    @else
                        <a href="{{ $attachment->download() }}" title="{{ $attachment->filename }}">{{ $attachment->filename }}</a>
                    @endif
                </li>
                @endforeach
            </ul>
            <div class="clearfix"></div>
        </div>
    </li>

    @foreach ($activities as $activity)
    @include('project/issue/activity/' . $activity->activity->activity, [
        'userActivity'    => $activity,
        'project'         => $project,
        'user'            => $activity->user,
        'comment'         => $activity->comment,
        'assigned'        => $activity->assignTo
    ])
    @endforeach
</ul>


@if($issue->status == 1)

<div class="new-comment" id="new-comment">
    @if(Auth::user()->permission('issue-modify'))

    <ul class="issue-actions">
        <li class="assigned-to">
            @lang('tinyissue.change_project')
            <a href="" class="current_project">
                {{ $project->name }}
            </a>
            <div class="dropdown">
                <ul>
                    @foreach ($projects as $aProject)
                    @if ($aProject->id !== $project->id)
                    <li>
                        <a href=""  data-project-id="{{ $aProject->id }}" data-issue-id="{{ $issue->id }}" class="change-project">
                            {{ $aProject->name }}
                        </a>
                    </li>
                    @endif
                    @endforeach
                </ul>
            </div>
        </li>
        <li class="assigned-to">
            @lang('tinyissue.assigned_to')

            @if($issue->assigned)
            <a href="" class="currently_assigned">
                {{ $issue->assigned->fullname }}
            </a>
            @else
            <a href="" class="currently_assigned">
                @lang('tinyissue.no_one')
            </a>
            @endif
            <div class="dropdown">
                <ul>
                    <li class="unassigned"><a href="" data-project-id="{{ $project->id }}" data-issue-id="{{ $issue->id }}" data-assign-id="0" class="assign-user user0{{ !$issue->assigned ? ' assigned' : '' }}">@lang('tinyissue.no_one')</a></li>
                    @foreach($project->users()->get() as $row)
                    <li><a href="" data-project-id="{{ $project->id }}" data-issue-id="{{ $issue->id }}" data-assign-id="{{ $row->id }}" class="assign-user user{{ $row->id . ($issue->assigned && $row->id == $issue->assigned->id ? ' assigned' : '') }}">{{ $row->fullname }}</a></li>
                    @endforeach
                </ul>
            </div>
        </li>
        <li>
            <a href="{{ $issue->to('status/0') }}" class="close-issue" data-message="@lang('tinyissue.close_issue_confirm')">@lang('tinyissue.close_issue')</a>
        </li>
    </ul>
    @endif

    <h4>
        @lang('tinyissue.comment_on_this_issue')
    </h4>

    {!! Form::form($commentForm, ['action'=> $issue->to('add_comment'),'secure'=>null]) !!}
</div>

</div>

@else
{!! Html::link($issue->to('status/1'), trans('tinyissue.reopen_issue')) !!}
@endif

@stop