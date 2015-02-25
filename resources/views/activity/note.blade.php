<li class="vlink"  data-url="{{ $userActivity->note->to() }}">

    <div class="tag">
        <span class="label label-note">@lang('tinyissue.note')</span>
    </div>

    <div class="data">
        <span class="note">
            {{ Html::trim($userActivity->note->body) }}
        </span>
        @lang('tinyissue.by')
        <strong>{{ $userActivity->user->fullname }}</strong>
        <span class="time">
            {{ Html::date($userActivity->activity->created_at) }}
        </span>
    </div>

    <div class="clearfix"></div>
</li>
