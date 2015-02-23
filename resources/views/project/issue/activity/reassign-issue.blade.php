<li id="comment{{ $activity->id }}" class="comment">

    <div class="insides">
        <div class="topbar">		
            <span class="label label-warning">@lang('tinyissue.label_reassigned')</span> @lang('tinyissue.to')
            @if($activity->action_id > 0)
            <strong>{{ $assigned->fullname }}</strong>
            @else
            <strong>@lang('tinyissue.no_one')</strong>
            @endif
            by
            <strong>{{ $user->fullname }}</strong>

            <span class="time">
                {{ Html::date($activity->created_at) }}
            </span>
        </div>

        <div class="clearfix"></div>
</li>
