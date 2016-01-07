
{{--<li class="signin"><a href="URL::to('login')">'tinyissue.signin')</a></li>--}}

<ul class="nav navbar-nav navbar-left">
    @foreach([
    'signin' => [
        'href' => '/',
        'title' => 'signin',
    ],
    'issues' => [
        'href' => '/issues',
        'title' => 'public_issues',
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
</ul>
