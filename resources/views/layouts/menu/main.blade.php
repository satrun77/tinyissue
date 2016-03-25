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
    ] as $name => $link)
        <li class='{{ $name }} @yield("nav/" . $name . "/class")'>
            <a href="{{ URL::to($link['href']) }}">
                @lang('tinyissue.' . $link['title'])
            </a>
        </li>
    @endforeach

    @permission('administration')
    <li class="settings"><a href="{{ URL::to('administration') }}">@lang('tinyissue.administration')</a></li>
    @endpermission

</ul>

<ul class="nav navbar-nav navbar-left visible-xs hidden-sm hidden-lg">
    <li class="settings"><a href="{{ URL::to('user/settings') }}">@lang('tinyissue.myprofile')</a></li>
    <li class="logout"><a href="{{ URL::to('/logout') }}">@lang('tinyissue.logout')</a></li>
</ul>

<ul class="nav navbar-nav navbar-right hidden-xs hidden-sm visible-md visible-lg">
    <li><span class="navbar-text">@lang('tinyissue.welcome')</span> <a
                href="{{ URL::to('user/settings') }}"
                class="user">{{ Auth::user()->firstname }}</a></li>
    <li class="logout"><a href="{{ URL::to('/logout') }}">@lang('tinyissue.logout')</a></li>
</ul>


