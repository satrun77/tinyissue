<h2>
    @lang('tinyissue.active_users')
    <span>@lang('tinyissue.active_users_description')</span>
</h2>

<ul>
    @forelse ($activeUsers as $user)
        <li>
            <h3>
                {{ $user->fullname }}<br />
                <span class="label label-{{ $user->role->className() }}">{{ $user->role->name }}</span>
            </h3>
        </li>
    @empty
    @endforelse
</ul>
