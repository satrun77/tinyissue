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
