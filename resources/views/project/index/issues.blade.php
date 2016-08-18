@if ($issues)
<div class="issue-quote">
    <strong>@lang('tinyissue.total_quote'):</strong>
    <span>{{ Html::duration($project->getTotalQuote()) }}</span>
</div>

<ul class="issues">
    @foreach ($issues as $issue)
    <li>
        <a href="" class="comments">{{ $issue->count_comments }}</a>

        <div class="issue-tags">
        @forelse($issue->tags as $tag)
                <label class="label" style="background: {{ $tag->bgcolor  or 'gray' }}">{!! Html::formatIssueTag($tag->fullname) !!}</label>
        @empty
        @endforelse
        </div>

        <a href="" class="id">#{{ $issue->id }}</a>
        <div class="data">
                <a href="{{ $issue->to() }}">{{ $issue->title }}</a>
                <div class="info">
                        @lang('tinyissue.created_by')
                        <strong>{{ $issue->user->firstname . ' ' . $issue->user->lastname }}</strong>
                        {{ Html::age($issue->created_at) }}

                        @if($issue->updated_by)
                        - @lang('tinyissue.updated_by') <strong>{{ $issue->updatedBy->firstname . ' ' . $issue->updatedBy->lastname }}</strong>
                        {{ Html::age($issue->updated_at) }}
                        @endif

                        @can('viewLockedQuote', $issue)
                        - @lang('tinyissue.time_quote') <strong>{{ Html::duration($issue->time_quote) }}</strong>
                        @endcan
                </div>
        </div>
    </li>
    @endforeach
</ul>
@else
<p>@lang('tinyissue.no_issues')</p>
@endif
