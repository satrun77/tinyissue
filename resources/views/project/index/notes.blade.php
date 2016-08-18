@if ($notes)
    <ul class="discussion notes">
        @foreach ($notes as $note)
            <li id="note{{ $note->id }}" class="note">
                <div class="insides">
                    <div class="topbar">
                        @can('update', $project)
                            <ul>
                                <li class="edit">
                                    <a href="{{ $project->to('edit_note/' . $note->id) }}" class="edit" data-note-id="{{ $note->id }}">Edit</a>
                                </li>
                                <li class="delete">
                                    <a href="{{ $project->to('delete_note/' . $note->id) }}" class="delete" data-message="@lang('tinyissue.confirm_delete_note')" data-note-id="{{ $note->id }}">Delete</a>
                                </li>
                            </ul>
                        @endcan
                        <strong>{{ $note->createdBy->fullname }}</strong>
                        @lang('tinyissue.noted') {{ Html::date($note->updated_at) }}
                    </div>

                    <div class="markdown content">
                        {!! Html::format($note->body) !!}
                    </div>

                    @can('update', $project)
                        <div class="form">
                            {!! Former::textarea('body')->value($note->body) !!}
                            <div class="right">
                                {!! Former::primary_button('save-btn')->value(trans('tinyissue.save'))->data_note_id($note->id)->addClass('save') !!}
                                {!! Former::info_button('cancel-btn')->value(trans('tinyissue.cancel'))->data_note_id($note->id)->addClass('cancel')!!}
                            </div>
                        </div>
                    @endcan
                </div>
                <div class="clearfix"></div>
            </li>
        @endforeach
    </ul>
@else
    <p>@lang('tinyissue.no_notes')</p>
@endif

@can('update', $project)
<div class="new-note" id="new-note">
    <h4>@lang('tinyissue.add_note')</h4>
    {!! Form::form($noteForm, ['action'=> $project->to('add_note'),'secure'=>null, 'method'=>'post']) !!}
</div>
@endcan
