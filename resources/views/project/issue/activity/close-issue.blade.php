<li id="comment{{ $activity->id }}" class="comment">
    <div class="insides">
        <div class="topbar">
            <div class="data">
                <span class="label label-success">@lang('tinyissue.label_closed')</span> @lang('tinyissue.by') <strong>{{ $user->fullname }}</strong> 
                <span class="time">
                    {{ Html::date($activity->created_at) }}
                </span>
                @if($issue->status == 0)
                <a href="{{ $issue->to('status/1') }}" class="">@lang('tinyissue.reopen')</a>				
                @endif
            </div>
        </div>
</li>
