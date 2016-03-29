
@macro('menu_item', $name, $href, $title, $classes = '')
<li class='{{ $name }} {{ $classes }} @yield("nav/" . $name . "/class")'>
    <a href="{{ URL::to($href) }}">
        @lang('tinyissue.' . $title)
    </a>
</li>
@endmacro

<ul class="nav navbar-nav navbar-left">
    @usemacro('menu_item', 'dashboard', 'dashboard', 'dashboard')
    @usemacro('menu_item', 'issues', 'user/issues', 'your_issues')
    @usemacro('menu_item', 'projects', 'projects', 'projects')
    @permission('administration')
    @usemacro('menu_item', 'settings', 'administration', 'administration')
    @endpermission

    @usemacro('menu_item', 'settings', 'user/settings', 'myprofile', ' visible-xs hidden-sm hidden-lg')
    @usemacro('menu_item', 'logout', 'logout', 'logout', ' visible-xs hidden-sm hidden-lg')
</ul>

<ul class="nav navbar-nav navbar-right hidden-xs hidden-sm visible-md visible-lg">
    <li>
        <span class="navbar-text">@lang('tinyissue.welcome')</span>
        <a href="{{ URL::to('user/settings') }}" class="user">{{ Auth::user()->firstname }}</a>
    </li>
    @usemacro('menu_item', 'logout', 'logout', 'logout')
</ul>
