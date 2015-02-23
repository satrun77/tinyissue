<li id="comment{{ $activity->id }}" class="comment">
    <div class="insides">
        <div class="topbar">
            <div class="data">
                <span class="label label-danger">@lang('tinyissue.label_reopened')</span> @lang('tinyissue.to') <strong>{{ $user->fullname }}</strong> 
                <span class="time">
                    {{ Html::date($activity->created_at) }}
                </span>		
            </div>
        </div>
</li>
