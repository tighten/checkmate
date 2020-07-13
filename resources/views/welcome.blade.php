@extends('layouts.app')

@section('content')
<div class="bg-frost font-sans relative z-0">
    <div class="max-w-xl mx-auto pt-8">
        <p class="mb-6 text-black-lighter">
            Showing versions for {{ $projects->count() }} active projects and packages
        </p>
        <div class="rounded-lg shadow">
            <ul class="bg-grey-blue-light flex list-reset p-4 rounded-t-lg border-grey border-b-2">
                <li class="w-2/6 font-semibold uppercase text-xs tracking-wide">Project / Package name</li>

                <li class="w-1/6 font-semibold uppercase text-xs tracking-wide">Version Constraint</li>

                <li class="w-1/6 font-semibold uppercase text-xs tracking-wide">Current Version</li>

                <li class="w-1/6 font-semibold uppercase text-xs tracking-wide">Prescribed Version</li>

                <li class="w-1/6 font-semibold uppercase text-xs tracking-wide">Status</li>

                <li class="w-1/6 font-semibold uppercase text-xs tracking-wide">Ignore</li>
            </ul>

            <section class="bg-white rounded-b-lg">
                @foreach ($projects as $project)
                    <ul class="flex list-reset p-4 border-t border-smoke">
                        <li class="w-2/6">
                            <a class="text-indigo no-underline text-md" href="{{ $project->github_url }}">
                                {{ $project->name }}
                            </a>
                        </li>

                        <li class="w-1/6 text-black-lightest">{{ $project->laravel_constraint }}</li>

                        <li class="w-1/6 text-black-lightest">{{ $project->laravel_version }}</li>

                        <li class="w-1/6 text-black-lightest">{{ $project->desired_laravel_version }}</li>

                        <li class="w-1/6 text-black-lightest">
                            @if ($project->is_behind_latest)
                                <span class="font-bold" style="color: red;">BEHIND</span>
                            @else
                                <span style="color: green;">CURRENT</span>
                            @endif
                        </li>

                        <li class="w-1/6">
                            <form action="{{ route('project.ignore', $project) }}" method="POST">
                                @method('PATCH')
                                @csrf
                                <input type="submit" value="Ignore" class="bg-indigo-light hover:bg-indigo-muted text-white px-3 py-1 rounded cursor-pointer">
                            </form>
                        </li>
                    </ul>
                @endforeach
            </section>
        </div>
    </div>
    <br><br>
</div>
@endsection
