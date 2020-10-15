<?php
$colorByStatus = [
    App\Project::STATUS_CURRENT => 'text-green-400',
    App\Project::STATUS_BEHIND => 'text-red-700',
    App\Project::STATUS_INSECURE => 'text-red-700 font-bold',
]
?>
@extends('layouts.app')

@section('content')
<div class="bg-gray-100 font-sans relative z-0">
    <div class="max-w-6xl mx-auto pt-8">
        <p class="mb-6 text-black-lighter">
            Showing versions for <span id="project_counter">{{ $projects->count() }}</span> active projects and packages
        </p>
        <div class="rounded-lg shadow">
            <ul class="bg-gray-400 flex list-reset p-4 rounded-t-lg border-gray border-b-2">
                <li class="w-2/6 font-semibold uppercase text-xs tracking-wide">Project / Package name</li>

                <li class="w-1/6 font-semibold uppercase text-xs tracking-wide">Version Constraint</li>

                <li class="w-1/6 font-semibold uppercase text-xs tracking-wide">Current Version</li>

                <li class="w-1/6 font-semibold uppercase text-xs tracking-wide">Prescribed Version</li>

                <li class="w-1/6 font-semibold uppercase text-xs tracking-wide">Status</li>

                <li class="w-1/6 font-semibold uppercase text-xs tracking-wide">Ignore</li>
            </ul>

            <section class="bg-white rounded-b-lg">
                @foreach ($projects as $project)
                    <ul id="project_{{ $project->id }}" class="flex list-reset p-4 border-t border-smoke {{ $project->status === App\Project::STATUS_CURRENT ? 'bg-green-100' : 'bg-red-100' }}">
                        <li class="w-2/6">
                            <a class="text-indigo-700 hover:text-indigo-900 no-underline text-md" href="{{ $project->github_url }}">
                                {{ $project->name }}
                            </a>
                        </li>

                        <li class="w-1/6 text-black-lightest">{{ $project->current_laravel_constraint }}</li>

                        <li class="w-1/6 text-black-lightest">{{ $project->current_laravel_version }}</li>

                        <li class="w-1/6 text-black-lightest">{{ $project->desired_laravel_version }}</li>

                        <li class="w-1/6 text-black-lightest">
                            <span class="{{ $colorByStatus[$project->status] }}">{{ strtoupper($project->status) }}</span>
                        </li>

                        <li class="w-1/6">
                            <button onClick="ignoreProject({{$project->id}});" type="button" class="bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1 rounded cursor-pointer">Ignore</button>
                        </li>
                    </ul>
                @endforeach
            </section>

        </div>
    </div>
    <br><br>
</div>
@endsection
