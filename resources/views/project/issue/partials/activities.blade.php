
@forelse($activities as $activity)
        @include('project/issue/activity/' . $activity->activity->activity, [
        'issue'           => $issue,
        'userActivity'    => $activity,
        'project'         => $project,
        'user'            => $activity->user,
        'comment'         => $activity->comment,
        'assigned'        => $activity->assignTo,
    ])
@empty
    <li class="no-records">{{ $no_data }}</li>
@endforelse
