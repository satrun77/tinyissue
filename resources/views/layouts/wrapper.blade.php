@extends('layouts.base')

@section('body')
    <div id="container" class="container-fluid">
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
                    @if (!Auth::guest())
                        @include('layouts/menu/main')
                    @else
                        @include('layouts/menu/public')
                    @endif
                </div>

            </div>
        </nav>

        <div class="main row">
            <div id="sidebar" class="col-sm-3 col-md-2">
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
    </div>
@stop
