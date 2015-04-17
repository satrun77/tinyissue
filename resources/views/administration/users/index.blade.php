@extends('layouts.wrapper')

@section('nav/dashboard/class')
active
@stop

@section('headingTitle')
    @lang('tinyissue.users')
@stop

@section('headingSubTitle')
    @lang('tinyissue.users_description')
@stop

@section('headingLink')
    {!! link_to('administration/users/add', trans('tinyissue.add_new_user')) !!}
@stop

@section('content')
<div id="users-list">

    @foreach($roles as $role)
    <h4>
        {{ $role->name }}
        <span>{{ $role->description }}</span>
    </h4>

    <ul>
        @foreach($role->users as $user)
        <li>
            <ul>
                @if(!$user->me())
                <li>
                    <a class="delete btn btn-danger" href="{{ URL::to('administration/users/delete/' . $user->id) }}" role="button" data-message="@lang('tinyissue.delete_user_confirm')">@lang('tinyissue.delete')</a>
                </li>
                @endif
                <li>
                    <a class="edit btn btn-default" role="button" href="{{ URL::to('administration/users/edit/' . $user->id) }}">@lang('tinyissue.edit')</a>
                </li>
            </ul>

            <a class="name" href="{{ URL::to('administration/users/edit/' . $user->id) }}">{{  $user->fullname }}</a>
        </li>
        @endforeach
    </ul>
    @endforeach

</div>
@stop
