@extends('layouts.base')

@section('style')
    {!! Html::style(elixir('css/tiny_login.css')) !!}
@stop

@section('body')

	<div id="overlay" class="login-overlay">
	</div>

    <div id="container" class="container-fluid">
		<div id="login" class="main">
            @yield('content')
        </div>
    </div>
@stop
