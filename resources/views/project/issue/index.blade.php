@extends('layouts.wrapper')

@section('scripts')
    {!! Html::script(elixir('js/tiny_project_issue.js')) !!}
@stop

@section('nav/projects/class')
    active
@stop

@section('headingTitle')
    @can('update', $issue)
        {!! link_to($issue->to('edit'), $issue->title, ['class' => 'edit-issue']) !!}
    @else
        {!! link_to($issue->to(), $issue->title) !!}
    @endcan
@stop

@section('headingSubTitle')
    @lang('tinyissue.on_project') {!! link_to($project->to(), $project->name) !!}
@stop

@section('headingLink')
    @if (!Auth::guest())
        {!! link_to($project->to('issue/new'), trans('tinyissue.new_issue')) !!}
    @endif
@stop

@section('content')
    @can('viewLockedQuote', $issue)
        <div class="issue-quote">
            <strong>@lang('tinyissue.time_quote'):</strong><span>{{ Html::duration($issue->time_quote) }}</span>
        </div>
    @endcan

    <div class="activity-tags">
        @if(!$issue->isOpen())
            <label class="label" style="background: lightgray">{!! Html::formatIssueTag('Closed') !!}</label>
        @endif

        @foreach($issue->tags as $tag)
            <label class="label"
                   style="background: {{ $tag->bgcolor or 'gray' }}">{!! Html::formatIssueTag($tag->name) !!}</label>
        @endforeach
    </div>

    <span class="clearfix"></span>

    <ul class="discussion comments">
        <li>
            <div class="insides">
                <div class="topbar">
                    <strong>{{ $issue->user->fullname }}</strong>
                    @lang('tinyissue.opened_this_issue') {{ Html::date($issue->created_at) }}
                </div>

                <div class="markdown content">
                    {!! Html::format($issue->body) !!}
                </div>

                <ul class="attachments">
                    @foreach($issue->attachments as $attachment)
                        <li>
                            @if($attachment->isImage())
                                <a href="{{ $attachment->download() }}" title="{{ $attachment->filename }}"><img
                                            src="{{ $attachment->display() }}" alt="{{ $attachment->filename }}"
                                            class="image"/></a>
                            @else
                                <a href="{{ $attachment->download() }}"
                                   title="{{ $attachment->filename }}">{{ $attachment->filename }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
                <div class="clearfix"></div>
            </div>
        </li>
    </ul>

    @if($issue->status == 1)
        @can('update', $issue)
            <div class="actions">
                <ul class="issue-actions">
                    <li class="assigned-to">
                        @lang('tinyissue.change_project')
                        <a href="" class="current current_project">
                            {{ $project->name }}
                        </a>
                        <div class="dropdown">
                            <ul>
                                @foreach ($projects as $aProject)
                                    @if ($aProject->id !== $project->id)
                                        <li>
                                            <a href="" data-project-id="{{ $aProject->id }}"
                                               data-issue-id="{{ $issue->id }}" class="change-project">
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
                            <a href="" class="current currently_assigned">
                                {{ $issue->assigned->fullname }}
                            </a>
                        @else
                            <a href="" class="current currently_assigned">
                                @lang('tinyissue.no_one')
                            </a>
                        @endif
                        <div class="dropdown">
                            <ul>
                                <li class="unassigned"><a href="" data-project-id="{{ $project->id }}"
                                                          data-issue-id="{{ $issue->id }}" data-assign-id="0"
                                                          class="assign-user user0{{ !$issue->assigned ? ' assigned' : '' }}">@lang('tinyissue.no_one')</a>
                                </li>
                                @foreach($usersCanFixIssues as $row)
                                    <li><a href="" data-project-id="{{ $project->id }}" data-issue-id="{{ $issue->id }}"
                                           data-assign-id="{{ $row->id }}"
                                           class="assign-user user{{ $row->id . ($issue->assigned && $row->id == $issue->assigned->id ? ' assigned' : '') }}">{{ $row->fullname }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                    <li>
                        <a href="{{ $issue->to('status/0') }}" class="close-issue"
                           data-message="@lang('tinyissue.close_issue_confirm')">@lang('tinyissue.close_issue')</a>
                    </li>
                </ul>
            </div>
        @endcan
    @else
        <div class="actions">
            <ul class="issue-actions">
                <li>
                    {!! Html::link($issue->to('status/1'), trans('tinyissue.reopen_issue')) !!}
                </li>
            </ul>
        </div>
    @endif

    <span class="clearfix"></span>

    <div class="activities">
        {!! Html::tab([['url' => $issue->to('comments'), 'page' => 'comments'], ['url' => $issue->to('activity'), 'page' => 'activity']], 'comments') !!}
        <div class="inside-tabs">
            <ul class="discussion comments">
                <li class="no-records">There are no comments yet on this issue.</li>
            </ul>
        </div>
    </div>

    @can('create', [\Tinyissue\Model\Project\Issue\Comment::class, $issue])
        <div class="new-comment" id="new-comment">
            <div class="comment-form-wrap">
                <h4>@lang('tinyissue.comment_on_this_issue')</h4>

                {!! Form::form($commentForm, ['action'=> $issue->to('add_comment'),'secure'=>null]) !!}
            </div>
        </div>
    @endcan

@stop
