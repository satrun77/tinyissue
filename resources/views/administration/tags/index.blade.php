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
        <h4><a href="{{ $group->to('edit') }}">{{ ucwords($group->name) }}</a></h4>
        <div class="list-group">
            @foreach($group->tags as $tag)
            <a href="{{ $tag->to('edit') }}" class="list-group-item"><span class="label" @if($tag->bgcolor) style="background:{{ $tag->bgcolor }}" @endif>{{ $tag->name }}</span></a>
            @endforeach
        </div>
    @endforeach

@stop
