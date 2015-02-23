@extends('layouts.wrapper')

@section('nav/dashboard/class')
active
@stop

@section('scripts')
{!! Html::script('js/dashboard.js') !!}
@stop

@section('contentTitle')
    {!! Html::toolbar('title', ['title' => 'dashboard', 'subTitle' => 'dashboard_description']) !!}
@stop

@section('content')
@foreach($projects as $project)
@if (count($project->activities) > 0)
{!! Html::startBox('blue-box', [$project->name, $project->to()]) !!}
<ul class="activity">
    @foreach ($project->activities->take(5) as $activity)
        @include('activity/' . $activity->activity->activity, [
                'userActivity'    => $activity,
                'project'         => $project,
        ])
    @endforeach
</ul>
{!! Html::endBox() !!}
@endif
@endforeach
@stop
