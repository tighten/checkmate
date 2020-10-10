@extends('layouts.app')

@section('content')
<div class="bg-gray-100 font-sans relative z-0">
    <div class="max-w-6xl mx-auto pt-8">
				<p class="mb-6 text-black-lighter">
            Showing <span id="project_counter">{{ $projects->count() }}</span> ignored projects and packages
        </p>
        <div class="rounded-lg shadow">
            <ul class="bg-gray-400 flex list-reset p-4 rounded-t-lg border-gray border-b-2">
                <li class="w-1/2 text-gray-darker font-semibold uppercase text-xs tracking-wide">Project / Package
                    name
                </li>
            </ul>

            <section class="bg-white rounded-b-lg">
                @foreach ($projects as $project)
                    <ul id="project_{{ $project->id }}" class="flex list-reset p-4 border-t border-smoke">
                        <li class="w-1/2">
                            <a class="text-indigo-700 hover:text-indigo-900 no-underline text-md" href="{{ $project->github_url }}">
                                {{ $project->name }}
                            </a>
                        </li>

                        <li class="w-1/2 text-right">
														<button onClick="unignoreProject({{$project->id}});" type="button" class="bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1 rounded cursor-pointer">Un-Ignore</button>
                        </li>
                    </ul>
                @endforeach
            </section>
        </div>
    </div>
    <br><br>
</div>
@endsection
