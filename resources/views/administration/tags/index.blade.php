@extends('layouts.wrapper')

@section('nav/admin/class')
active
@stop

@section('headingTitle')
    @lang('tinyissue.tags')
@stop

@section('headingSubTitle')
    @lang('tinyissue.tags_list')
@stop

@section('headingLink')
    {!! link_to('administration/tag/new', trans('tinyissue.add_tag')) !!}
@stop

@section('content')

    @foreach($tags as $group)
        <h4>@lang('tinyissue.' . $group->name)</h4>
        <ul class="list-group">
            @foreach($group->tags as $tag)
                <li class="list-group-item">
                    <a href="{{ $tag->to('delete') }}" class="tag delete has-event" data-message="@lang('tinyissue.confirm_delete_tag')" data-tag-id="{{ $tag->id }}">
                        @lang('tinyissue.remove')
                    </a>
                    <a href="{{ $tag->to('edit') }}">
                        <span class="label" style="background:{{ $tag->bgcolor or 'gray' }}">{{ $tag->name }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endforeach

@stop
