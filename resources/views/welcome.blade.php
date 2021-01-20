<?php
use App\Project;

$colorByStatus = [
    Project::STATUS_CURRENT => 'text-green-400',
    Project::STATUS_BEHIND => 'text-orange-600',
    Project::STATUS_INSECURE => 'text-red-700 font-bold',
];
$backgroundColorByStatus = [
    Project::STATUS_CURRENT => 'bg-green-100',
    Project::STATUS_BEHIND => 'bg-orange-100',
    Project::STATUS_INSECURE => 'bg-red-100',
];
?>
@extends('layouts.app')

@section('content')
<div class="relative z-0 font-sans bg-gray-100">
    <div class="max-w-6xl pt-8 mx-auto">
        <p class="mb-6 text-black-lighter">
            Showing versions for <span id="project_counter">{{ $projects->count() }}</span> active projects and packages
        </p>
        <div class="rounded-lg shadow">
            <ul class="flex p-4 bg-gray-400 border-b-2 rounded-t-lg list-reset border-gray">
                <li class="w-2/6 text-xs font-semibold tracking-wide uppercase">Project / Package name</li>

                <li class="w-1/6 text-xs font-semibold tracking-wide uppercase">Version Constraint</li>

                <li class="w-1/6 text-xs font-semibold tracking-wide uppercase">Current Version</li>

                <li class="w-1/6 text-xs font-semibold tracking-wide uppercase">Prescribed Version</li>

                <li class="w-1/6 text-xs font-semibold tracking-wide uppercase">Status</li>

                <li class="w-1/6 text-xs font-semibold tracking-wide uppercase">Ignore</li>
            </ul>

            <section class="bg-white rounded-b-lg">
                @foreach ($projects as $project)
                    <ul id="project_{{ $project->id }}" class="flex list-reset p-4 border-t border-smoke {{ $backgroundColorByStatus[$project->status] }}">
                        <li class="w-2/6">
                            <a class="text-indigo-700 no-underline hover:text-indigo-900 text-md" href="{{ $project->github_url }}">
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
                            <button onClick="ignoreProject({{$project->id}});" type="button" class="px-3 py-1 text-white bg-indigo-600 rounded cursor-pointer hover:bg-indigo-500">Ignore</button>
                        </li>
                    </ul>
                @endforeach
            </section>

        </div>
    </div>
    <br><br>
</div>
@endsection
