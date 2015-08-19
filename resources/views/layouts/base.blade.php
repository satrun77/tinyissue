<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/Icon.png') }}"/>
    <title>@yield('title', 'Tiny issue')</title>
    <script>
        var TINY = {
            @if (!empty($project))
            projectId: '{{ $project->id }}',
            @endif
            baseUrl: '{{ asset('') }}',
            token: '{{ csrf_token() }}',
            basePath: '{{ Request::getBaseUrl() }}/'
        };
    </script>
    @yield('style', Html::style(elixir('css/tiny.css')))
</head>
<body>
@yield('body', '')

@section('footer')
    <a href=""
       class="global-notice {{{ Session::has('notice-error') ? 'global-error' : '' }}}">{{ Session::get('notice', Session::get('notice-error')) }}</a>
    <a href="" class="global-saving"><span>@lang('tinyissue.saving')</span></a>

    @yield('scripts', Html::script(elixir('js/tiny.js')))
@show
</body>
</html>
