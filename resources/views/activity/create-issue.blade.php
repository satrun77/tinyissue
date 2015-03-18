<li class="vlink"  data-url="{{ $userActivity->issue->to() }}">
    
    <div class="tag">
        <span class="label label-danger">@lang('tinyissue.label_created')</span>
    </div>

    <div class="data">
        <a href="{{ $userActivity->issue->to() }}">{{ $userActivity->issue->title }}</a> @lang('tinyissue.was_created_by')
        <strong>{{ $userActivity->user->fullname }}</strong>

        <span class="time">
            {{ Html::date($userActivity->created_at) }}
        </span>
    </div>

    <div class="clearfix"></div>
</li>
