@extends('layouts.wrapper')

@section('nav/dashboard/class')
active
@stop

@section('scripts')
{{ Html::script('js/dashboard.js') }}
@stop

@section('contentTitle')
    {{ Html::heading('title', ['title' => 'dashboard', 'subTitle' => 'dashboard_description']) }}
@stop

@section('content')
    @foreach($userActivities->get() as $userProject)
        {{ Html::startBox('blue-box', [$userProject->name, $userProject->to()], ['data-project' => $userProject->id, 'class' => 'project-activity']) }}
        @include('project/index/activity', ['project' => $userProject, 'activities' => $userProject->activities()->with(['issue', 'issue.user'])->take(5)->get()])
        {{ Html::endBox($userProject->to(), $userProject->name) }}
    @endforeach
@stop
