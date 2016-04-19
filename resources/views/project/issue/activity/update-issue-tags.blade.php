@if (!empty($activity->data['added_tags']) || !empty($activity->data['removed_tags']))
    <li id="comment{{ $activity->id }}" class="comment">
        <div class="insides">
            <div class="topbar">
                <div class="data">
                    <div class="activity-tags">
                        @if (!empty($activity->data['added_tags']))
                            @foreach($activity->data['added_tags'] as $tag)
                                <label class="label"
                                       style="background: {{ $tag['bgcolor'] or 'gray' }}">
                                    {!! Html::formatIssueTag($tag['name']) !!}
                                </label>
                            @endforeach
                            <span class="text">@lang('tinyissue.tag_added', ['s' => count($activity->data['added_tags']) > 1? 's' : ''])</span>
                        @endif
                    </div>

                    <div class="activity-tags">
                        @if (!empty($activity->data['removed_tags']))
                            @foreach($activity->data['removed_tags'] as $tag)
                                <label class="label"
                                       style="background: {{ $tag['bgcolor'] or 'gray' }}">
                                    {!! Html::formatIssueTag($tag['name']) !!}
                                </label>
                            @endforeach
                            <span class="text">@lang('tinyissue.tag_removed', ['s' => count($activity->data['removed_tags']) > 1? 's' : ''])</span>
                        @endif
                    </div>
                    @lang('tinyissue.by')
                    <strong>{{ $userActivity->user->fullname }}</strong>
                <span class="time">
                    {{ Html::date($activity->created_at) }}
                </span>
                </div>
            </div>
        </div>
    </li>
@endif
