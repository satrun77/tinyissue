<li id="comment{{ $comment->id }}" class="comment">
    <div class="insides">
        <div class="topbar">
            @if(Auth::user()->permission('issue-modify'))
            <ul>
                <li class="edit-comment">
                    <a href="{{ Url::to('project/issue/edit_comment/' . $comment->id) }}" class="edit" data-comment-id="{{ $comment->id }}">Edit</a>
                </li>
                <li class="delete-comment">
                    <a href="{{ Url::to('project/issue/delete_comment/' . $comment->id) }}" class="delete" data-message="@lang('tinyissue.confirm_delete_comment')" data-comment-id="{{ $comment->id }}">Delete</a>
                </li>
            </ul>
            @endif
            <strong>{{ $user->fullname }}</strong>
            @lang('tinyissue.commented') {{ Html::date($comment->updated_at) }}
        </div>

        <div class="issue">
            {!! Html::format($comment->comment) !!}
        </div>

        @if(Auth::user()->permission('issue-modify'))
        <div class="comment-edit">
            {!! Former::textarea('body')->value(stripslashes($comment->comment)) !!}
            <div class="right">
                {!! Former::primary_button('save-btn')->value(trans('tinyissue.save'))->data_comment_id($comment->id)->addClass('save') !!}
                {!! Former::info_button('cancel-btn')->value(trans('tinyissue.cancel'))->data_comment_id($comment->id)->addClass('cancel')!!}
            </div>
        </div>
        @endif

        <ul class="attachments">
            @foreach($comment->attachments as $attachment)
                <li>
                    @if($attachment->setRelation('issue', $issue) && $attachment->isImage())
                        <a href="{{ $attachment->download() }}" title="{{ $attachment->filename }}"><img src="{{ $attachment->display() }}" alt="{{ $attachment->filename }}" class="image"/></a>
                    @else
                        <a href="{{ $attachment->download() }}" title="{{ $attachment->filename }}">{{ $attachment->filename }}</a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    <div class="clearfix"></div>
</li>
