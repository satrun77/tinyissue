<li class="vlink" data-url="{{ $userActivity->issue->to() }}">

    <div class="tag">
        <span class="label label-tag">@lang('tinyissue.tag_update')</span>
    </div>

    <div class="data">
            @if(($countAddedTags = $userActivity->dataCollection('added_tags')->count()) > 0)
                <div class="activity-tags">
                @foreach($userActivity->data['added_tags'] as $tag)
                    <label class="label" style="background: {{ $tag['bgcolor'] or 'gray' }}">{!! Html::formatIssueTag($tag['name']) !!}</label>
                @endforeach
                @lang('tinyissue.tag_added', ['s' => $countAddedTags > 1? 's' : ''])
                </div>
            @endif
            @if(($countRemovedTags = $userActivity->dataCollection('removed_tags')->count()) > 0)
                <div class="activity-tags">
                @foreach($userActivity->data['removed_tags'] as $tag)
                    <label class="label" style="background: {{ $tag['bgcolor'] or 'gray' }}">{!! Html::formatIssueTag($tag['name']) !!}</label>
                @endforeach
                @lang('tinyissue.tag_removed', ['s' => $countAddedTags > 1? 's' : ''])
                </div>
            @endif

        @lang('tinyissue.in')
        <a href="{{ $userActivity->issue->to() }}">{{ $userActivity->issue->title }}</a> @lang('tinyissue.by')
        <strong>{{ $userActivity->user->fullname }}</strong>
        <span class="time">
            {{ Html::date($userActivity->created_at) }}
        </span>
    </div>

    <div class="clearfix"></div>
</li>
