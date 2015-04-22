
@macro('progress_color', $progress)
@if ($progress < 50)
    progress-bar-danger
@elseif ($progress >= 50 && $progress < 60)
    progress-bar-warning
@else
    progress-bar-success
@endif
@endmacro

<div class="progress">
    <div class="progress-bar @usemacro('progress_color', (int)$progress)" role="progressbar" aria-valuenow="{{ $progress }}"
         aria-valuemin="0" aria-valuemax="100">{{ $text or '' }}
    </div>
</div>
