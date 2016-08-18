<h2>
    @can('update', $project)
    <a href="{{ $project->to('edit') }}" class="edit">@lang('tinyissue.edit')</a>
    @endcan

    {!! Html::link($project->to(), $project->name) !!}
    <span>@lang('tinyissue.assign_users_and_edit_the_project')</span>
</h2>

<ul class="content">
    <li>
        <a href="{{ $project->to('issues') }}">
        <span>
            {{ $open_issues_count }}
            @lang('tinyissue.open_issues')
        </span>
        </a>
    </li>
    <li>
        <a href="{{ $project->to('issues') }}/0">
        <span>
            {{ $closed_issues_count }}
            @lang('tinyissue.closed_issues')
        </span>
        </a>
    </li>
</ul>

<h2>
    @lang('tinyissue.assigned_users')
    <span>@lang('tinyissue.assigned_users_description')</span>
</h2>

<div class="content">
    <ul class="sidebar-users">
        @foreach($project_users as $row)
            <li id="project-user{{ $row->id }}">
                @can('update', $project)
                <a href="{{ $project->to('unassign_user') }}" data-message="@lang('tinyissue.confirm_unassign_user')"
                   data-user-id="{{ $row->id }}" data-project-id="{{ $project->id }}"
                   class="delete delete-from-project">@lang('tinyissue.remove')</a>
                @endcan
                {{ $row->fullname }}
            </li>
        @endforeach
    </ul>
</div>

@can('update', $project)

<div class="content">
    {!! Former::text('add-user-project')->placeholder(trans('tinyissue.assign_a_user'))->setAttribute('data-project-id', $project->id) !!}
</div>

<h2>
    @lang('tinyissue.export_issues')
    <span>@lang('tinyissue.export_issues_description')</span>
</h2>

<div class="content">
    {!! Form::form($exportForm, ['action'=> $project->to('export_issues'), 'method'=>'POST', 'id'=>'export-project-issues']) !!}
</div>
@endcan
