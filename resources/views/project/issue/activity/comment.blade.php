<li id="comment{{ $comment->id }}" class="comment">
    <div class="insides">
        <div class="topbar">
            @if($comment->canEdit(auth()->user()))
            <ul>
                <li class="edit">
                    <a href="{{ url('project/issue/edit_comment/' . $comment->id) }}" class="has-event edit" data-comment-id="{{ $comment->id }}">Edit</a>
                </li>
                <li>
                    <a href="{{ url('project/issue/delete_comment/' . $comment->id) }}" class="has-event delete" data-message="@lang('tinyissue.confirm_delete_comment')" data-comment-id="{{ $comment->id }}">Delete</a>
                </li>
            </ul>
            @endif
            <strong>{{ $user->fullname }}</strong>
            @lang('tinyissue.commented') {{ Html::date($comment->updated_at) }}
        </div>

        <div class="markdown content">
            {!! Html::format($comment->comment) !!}
        </div>

        @permission('issue-modify')
        <div class="form">
            {!! Former::textarea('body')->value(stripslashes($comment->comment)) !!}
            <div class="right">
                {!! Former::primary_button('save-btn')->value(trans('tinyissue.save'))->data_comment_id($comment->id)->addClass('save') !!}
                {!! Former::info_button('cancel-btn')->value(trans('tinyissue.cancel'))->data_comment_id($comment->id)->addClass('cancel')!!}
            </div>
        </div>
        @endpermission

        <ul class="attachments">
            @foreach($comment->attachments as $attachment)
                <li>
                    @if($attachment->canEdit(auth()->user()))
                        <a href="{{ $attachment->toDelete() }}" class="delete delete-attach" data-message="@lang('tinyissue.confirm_delete_attachment')" data-tag-id="{{ $attachment->id }}">
                            @lang('tinyissue.remove')
                        </a>
                    @endif

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
