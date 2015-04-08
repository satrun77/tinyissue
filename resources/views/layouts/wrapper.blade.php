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
                <nav id="header" class="navbar-fixed-top navbar navbar-default">
                    <div class="container-fluid">
                        <div class="col-sm-3 col-md-2 navbar-header">
                            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                                    data-target="#menu-navbar-collapse">
                                <span class="sr-only">Toggle navigation</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                            <a class="navbar-brand" href="{{ URL::to('/') }}"><img width="87px" height="23px"
                                                                                   alt="Tiny Issue"
                                                                                   src="{{ asset('images/layout/tinyissue.png') }}"></a>
                        </div>
                        <div class="collapse navbar-collapse" id="menu-navbar-collapse">
                            <ul class="nav navbar-nav navbar-left">
                                @foreach([
                                'dashboard' => [
                                    'href' => 'dashboard',
                                    'title' => 'dashboard',
                                ],
                                'issues' => [
                                    'href' => 'user/issues',
                                    'title' => 'your_issues',
                                ],
                                'projects' => [
                                    'href' => 'projects',
                                    'title' => 'projects',
                                ],
                                'settings' => [
                                    'href' => 'user/settings',
                                    'title' => 'settings',
                                ],
                                ] as $name => $link)
                                    <li class='{{ $name }} @yield("nav/" . $name . "/class")'>
                                        <a href="{{ URL::to($link['href']) }}">
                                            @lang('tinyissue.' . $link['title'])
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <ul class="nav navbar-nav navbar-right visible-lg">
                                <li><span class="navbar-text">@lang('tinyissue.welcome'),</span> <a
                                            href="{{ URL::to('user/settings') }}"
                                            class="user">{{ Auth::user()->firstname }}</a></li>
                                @permission('administration')
                                <li><a href="{{ URL::to('administration/users') }}">@lang('tinyissue.users')</a></li>
                                <li><a href="{{ URL::to('administration') }}">@lang('tinyissue.administration')</a></li>
                                @endpermission
                                <li class="logout"><a href="{{ URL::to('/logout') }}">@lang('tinyissue.logout')</a></li>
                            </ul>
                        </div>
                    </div>
                </nav>

                <div class="main row">
                <div id="sidebar" class="col-sm-3 col-md-2 hidden-xs">
                    <div class="inside">
                        @if (!isset($sidebar))
                        @include('layouts/sidebar/default')
                        @else
                        @include('layouts/sidebar/' . $sidebar)
                        @endif
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
