@extends('layouts.app')

@section('content')
<div class="relative z-0 font-sans bg-gray-100">
    <div class="max-w-6xl pt-8 mx-auto">
        <a href="{{ route('projects.index') }}" class="float-right text-indigo-700 no-underline hover:text-indigo-900 hover:underline">View active projects</a>
        <p class="mb-6 text-black-lighter">
            Showing <span id="project_counter">{{ $projects->count() }}</span> ignored projects and packages
        </p>
        <div class="rounded-lg shadow">
            <ul class="flex p-4 bg-gray-400 border-b-2 rounded-t-lg list-reset border-gray">
                <li class="w-1/2 text-xs font-semibold tracking-wide uppercase text-gray-darker">Project / Package
                    name
                </li>
            </ul>

            <section class="bg-white rounded-b-lg">
                @foreach ($projects as $project)
                    <ul id="project_{{ $project->id }}" class="flex p-4 border-t list-reset border-smoke">
                        <li class="w-1/2">
                            <a class="text-indigo-700 no-underline hover:text-indigo-900 text-md" href="{{ $project->github_url }}">
                                {{ $project->name }}
                            </a>
                        </li>

                        <li class="w-1/2 text-right">
                            <button onClick="unignoreProject({{$project->id}});" type="button" class="px-3 py-1 text-white bg-indigo-600 rounded cursor-pointer hover:bg-indigo-500">Un-Ignore</button>
                        </li>
                    </ul>
                @endforeach
            </section>
        </div>
    </div>
    <br><br>
</div>
@endsection
