<h2>
    @lang('tinyissue.active_users')
    <span>@lang('tinyissue.active_users_description')</span>
</h2>

<ul>
    @forelse ($activeUsers as $user)
        <li>
            <h3>
                <span>{{ $user->fullname }}</span>
            </h3>
        </li>
    @empty
    @endforelse
</ul>
