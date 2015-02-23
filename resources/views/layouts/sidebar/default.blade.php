<h2>
    @if(Auth::user()->permission('project-create'))
    <a href="{{ URL::to('projects/new') }}" class="add" title="New Project">@lang('tinyissue.new')</a>
    @endif
    @lang('tinyissue.active_projects')
    <span>@lang('tinyissue.active_projects_description')</span>
</h2>

<ul>
    @forelse ($projects as $project)
    <li>
        <a href="{{ $project->to() }}">{{ $project->name }}</a>
    </li>
    @empty
    @endforelse
</ul>