<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tiny issue')</title>
    {!! Html::style('css/error.css') !!}
    @yield('styles')
</head>
<body>
<div id="main">
@section('content')
    <h1>@yield('header1')</h1>

    <h2>@yield('header2')</h2>

    <h3>What does this mean?</h3>

    <p>
        @yield('message')
    </p>

    <p>
        Perhaps you would like to go to our {!! Html::link('/', 'home page') !!}
    </p>
@show
</div>
@yield('scripts')
</body>
</html>
