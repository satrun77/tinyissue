@extends('layouts.base')

@section('style')
    {!! Html::style(elixir('css/tiny_error.css')) !!}
@stop

@section('body')
    <div id="main">
        <h1>@yield('header1')</h1>

        <h2>@yield('header2')</h2>

        <h3>What does this mean?</h3>

        <p>
            @yield('message')
        </p>

        <p>
            @section('message2')
                Perhaps you would like to go to our {!! Html::link('/', 'home page') !!}
            @show
        </p>
    </div>
@stop

@section('footer')

@stop
