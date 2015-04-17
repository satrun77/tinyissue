<li  class="vlink"  data-url="{{ $userActivity->issue->to() }}#comment{{ $userActivity->comment->id }}">
    
    <div class="tag">
        <span class="label label-info">@lang('tinyissue.label_comment')</span>
    </div>

    <div class="data">
        <span class="markdown comment">
            {{ Html::trim($userActivity->comment->comment) }}
        </span>
        @lang('tinyissue.by')
        <strong>{{ $userActivity->user->fullname }}</strong> @lang('tinyissue.on_issue') <a href="{{ $userActivity->issue->to() }}">{{ $userActivity->issue->title }}</a>
        <span class="time">
            {{ Html::date($userActivity->created_at) }}
        </span>
    </div>

    <div class="clearfix"></div>
</li>
