<li @if($active === 'activity') class="active"@endif>
    <a href="{{ $project->to() }}">@lang('tinyissue.activity')</a>
</li>
<li @if($active === 'open_issues') class="active"@endif>
    <a href="{{ $project->to('issues') }}">{{ $open_issues_count }} @lang('tinyissue.open_issues')</a>
</li>
<li @if($active === 'closes_issues') class="active"@endif>
    <a href="{{ $project->to('issues') }}/0">{{ $closed_issues_count }} @lang('tinyissue.closed_issue')</a>
</li>
<li @if($active === 'assign_issues' || $active === 'created_issues') class="active"@endif>
    @if(isset($assigned_issues_count))
        <a href="{{ $project->to('assigned') }}">{{ $assigned_issues_count }} @lang('tinyissue.issue_assigned_to_you')</a>
    @elseif(isset($created_issues_count))
        <a href="{{ $project->to('created') }}">{{ $created_issues_count }} @lang('tinyissue.issue_created_by_you')</a>
    @endif
</li>
<li @if($active === 'notes') class="active"@endif>
    <a href="{{ $project->to('notes') }}">{{ $notes_count }} @lang('tinyissue.notes')</a>
</li>
