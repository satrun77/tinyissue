@extends('layouts.wrapper')

@section('nav/dashboard/class')
active
@stop

@section('contentTitle')
    {!! Html::toolbar('title', ['title' => 'administration', 'subTitle' => 'administration_description']) !!}
@stop

@section('content')

<ul class="list-group">
    @foreach([
                'total_users' => $users,
                'active_projects' => $active_projects,
                'archived_projects' => $archived_projects,
                'open_issues' => $open_issues,
                'closed_issues' => $closed_issues,
                'version' => 'v' . config('tinyissue.version'),
                'version_release_date' => config('tinyissue.release_date'),
             ] as $countLabel => $countValue)
    <li class="list-group-item">
      <span class="badge">{{ $countValue }}</span>
      @lang('tinyissue.' . $countLabel)
    </li>
    @endforeach
</ul>

@stop
