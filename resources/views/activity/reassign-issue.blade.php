<li class="vlink"  data-url="{{ $userActivity->issue->to() }}">
    
    <div class="tag">
        <span class="label label-warning">@lang('tinyissue.label_reassigned')</span>
    </div>

    <div class="data">
        <a href="{{ $userActivity->issue->to() }}">{{ $userActivity->issue->title }}</a> @lang('tinyissue.was_reassigned_to')
        @if($userActivity->action_id > 0)
        <strong>{{ $userActivity->assignTo->fullname }}</strong>
        @else
        <strong>@lang('tinyissue.no_one')</strong>
        @endif
        @lang('tinyissue.by')
        <strong>{{ $userActivity->user->fullname }}</strong>

        <span class="time">
            {{ Html::date($userActivity->created_at) }}
        </span>
    </div>

    <div class="clearfix"></div>
</li>
