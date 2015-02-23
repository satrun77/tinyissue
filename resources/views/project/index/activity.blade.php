@if ($activities) 
<ul class="activity">
    @foreach ($activities as $activity)
        @include('activity/' . $activity->activity->activity, [
                'userActivity'    => $activity,
                'project'         => $project,
        ])
    @endforeach
</ul>
@else 
<p>@lang('tinyissue.no_activity')</p>
@endif
