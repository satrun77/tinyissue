@extends('layouts.wrapper')

@section('nav/dashboard/class')
active
@stop

@section('headingTitle')
    @lang('tinyissue.administration')
@stop

@section('headingSubTitle')
    @lang('tinyissue.administration_description')
@stop

@macro('list_item', $count, $label, $simple = true)
<li class="list-group-item">
    <span class="badge">{{ $count }}</span>
    @if($simple)
        @lang('tinyissue.' . $label)
    @else
        {!! $label !!}
    @endif
</li>
@endmacro

@section('content')
<ul class="list-group">
    @usemacro('list_item', $users, link_to('administration/users', trans('tinyissue.total_users')), false)
    @usemacro('list_item', $active_projects, 'active_projects')
    @usemacro('list_item', $archived_projects, 'archived_projects')
    @usemacro('list_item', $tags, link_to('administration/tags', trans('tinyissue.tags')), false)
    @usemacro('list_item', $open_issues, 'open_issues')
    @usemacro('list_item', $closed_issues, 'closed_issues')
    @usemacro('list_item', 'v' . config('tinyissue.version'), 'version')
    @usemacro('list_item', config('tinyissue.release_date'), 'version_release_date')
    @usemacro('list_item', '&#x276f;', link_to('administration/settings', trans('tinyissue.settings')), false)
</ul>
@stop
