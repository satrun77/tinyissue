<h2>
    @permission('project-modify')
    <a href="{{ $project->to('edit') }}" class="edit">@lang('tinyissue.edit')</a>
    @endpermission

    {!! Html::link($project->to(), $project->name) !!}
    <span>@lang('tinyissue.assign_users_and_edit_the_project')</span>
</h2>
<ul class="content">
    <li><a href="{{ $project->to('issues') }}">
        @if (!empty($open_issues_count))
        {{ $open_issues_count }}
        @else
        {{ $project->openIssuesCount()->count() }}
        @endif
        @lang('tinyissue.open_issues')</a></li>
    <li><a href="{{ $project->to('issues') }}/0">
        @if (!empty($closed_issues_count))
        {{ $closed_issues_count }}
        @else
        {{ $project->closedIssuesCount()->count() }}
        @endif
        @lang('tinyissue.closed_issues')</a></li>
</ul>

<h2>
    @lang('tinyissue.assigned_users')
    <span>@lang('tinyissue.assigned_users_description')</span>
</h2>

<div class="content">
<ul class="sidebar-users">
    @foreach($project->users()->get() as $row)
    <li id="project-user{{ $row->id }}">
        @permission('project-modify')
        <a href="{{ $project->to('unassign_user') }}" data-message="@lang('tinyissue.confirm_unassign_user')" data-user-id="{{ $row->id }}" data-project-id="{{ $project->id }}" class="delete delete-from-project">@lang('tinyissue.remove')</a>
        @endpermission
        {{ $row->fullname }}
    </li>
    @endforeach
</ul>

@permission('project-modify')
{!! Former::text('add-user-project')->placeholder(trans('tinyissue.assign_a_user'))->setAttribute('data-project-id', $project->id) !!}
</div>

<h2>
    @lang('tinyissue.export_issues')
    <span>@lang('tinyissue.export_issues_description')</span>
</h2>

<div class="content">
{!! Form::form($exportForm, ['action'=> $project->to('export_issues'), 'method'=>'POST', 'id'=>'export-project-issues']) !!}
@endpermission

</div>
