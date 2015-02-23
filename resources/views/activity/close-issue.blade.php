<li class="vlink" data-url="{{ $userActivity->issue->to() }}">

	<div class="tag">
		<span class="label label-success">@lang('tinyissue.label_closed')</span>
	</div>

	<div class="data">
		<a href="{{ $userActivity->issue->to() }}">{{ $userActivity->issue->title }}</a> @lang('tinyissue.was_closed_by') <strong>{{ $userActivity->user->fullname }}</strong>
		<span class="time">
            {{ Html::date($userActivity->activity->created_at) }}
		</span>
	</div>

	<div class="clearfix"></div>
</li>
