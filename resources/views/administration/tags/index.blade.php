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
        <div class="list-group">
            @foreach($group->tags as $tag)
            <a href="{{ $tag->to('edit') }}" class="list-group-item"><span class="label" style="background:{{ $tag->bgcolor or 'gray' }}">{{ $tag->name }}</span></a>
            @endforeach
        </div>
    @endforeach

@stop
