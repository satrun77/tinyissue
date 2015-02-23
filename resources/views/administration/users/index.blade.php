@extends('layouts.wrapper')

@section('nav/dashboard/class')
active
@stop

@section('contentTitle')    
{!! Html::toolbar('add_user', ['title' => 'users', 'subTitle' => 'users_description']) !!}
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
                <li class="delete">
                    <a href="{{ URL::to('administration/users/delete/' . $user->id) }}" data-message="@lang('tinyissue.delete_user_confirm')" class="button tiny error right">@lang('tinyissue.delete')</a>
                </li>
                @endif
                <li class="edit">
                    <a href="{{ URL::to('administration/users/edit/' . $user->id) }}">@lang('tinyissue.edit')</a>
                </li>
            </ul>

            <a class="name" href="{{ URL::to('administration/users/edit/' . $user->id) }}">{{  $user->fullname }}</a>
        </li>
        @endforeach
    </ul>
    @endforeach

</div>
@stop
