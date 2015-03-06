<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--        <link rel="shortcut icon" href="/favicon.ico" />
                <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
                <link rel="apple-touch-icon" href="/img/Icon.png"/>-->
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
        {!! Html::style('css/base.css') !!}
        @section('styles')
        {!! Html::style('css/app.css') !!}
        @show
    </head>
    <body>
        <div id="container" class="container-fluid">
            @if (Auth::check())
            <div id="header" class="navbar-fixed-top">
                <ul class="nav-right">
                    <li>@lang('tinyissue.welcome'), <a href="{{ URL::to('user/settings') }}" class="user">{{ Auth::user()->firstname }}</a></li>
                    @if (Auth::user()->permission('administration'))
                    <li><a href="{{ URL::to('administration/users') }}">@lang('tinyissue.users')</a></li>
                    <li><a href="{{ URL::to('administration') }}">@lang('tinyissue.administration')</a></li>
                    @endif
                    <li class="logout"><a href="{{ URL::to('user/logout') }}">@lang('tinyissue.logout')</a></li>
                </ul>

                <a href="{{ URL::to(' / ') }}" class="logo">Tiny Issue</a>

                <ul class="nav">
                    <li class='dashboard @yield("nav/dashboard/class")'><a href="{{ URL::to('dashboard') }}">@lang('tinyissue.dashboard')</a></li>
                    <li class='issues @yield("nav/issues/class")'><a href="{{ URL::to('user/issues') }}">@lang('tinyissue.your_issues')</a></li>
                    <li class='projects @yield("nav/projects/class")'><a href="{{ URL::to('projects') }}">@lang('tinyissue.projects')</a></li>
                    <li class='settings @yield("nav/settings/class")'><a href="{{ URL::to('user/settings') }}">@lang('tinyissue.settings')</a></li>
                </ul>
            </div>

            <div class="main row">
                <div id="sidebar" class="col-sm-3 col-md-2">
                    <div class="inside">
                        @if (!isset($sidebar))
                        @include('layouts/sidebar/default')
                        @else
                        @include('layouts/sidebar/' . $sidebar)
                        @endif
                        @show
                    </div>
                </div>

                <div id="content" class="col-sm-9 col-md-10">
                    <div class="inside">
                        <h3>
                            @yield('headingLink')
                            <span class="title">@yield('headingTitle')</span>
                            <span class="subtitle">@yield('headingSubTitle')</span>
                        </h3>

                        <div class="pad container-fluid" id='inner-content'>
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>

            @else
            <div id="login" class="main">
                @yield('content')
            </div>
            @endif
        </div>

        <a href="" class="global-notice {{{ Session::has('notice-error') ? 'global-error' : '' }}}">{{ Session::get('notice', Session::get('notice-error')) }}</a>
        <a href="" class="global-saving"><span>@lang('tinyissue.saving')</span></a>

        {!! Html::script('js/jquery.js') !!}
        {!! Html::script('js/jquery.cookie.js') !!}
        {!! Html::script('js/jquery-ui.js') !!}
        {!! Html::script('js/app.js') !!}
        @yield('scripts')
    </body>
</html>
