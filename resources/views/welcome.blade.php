@extends('layouts.app')

@section('content')
<div class="bg-gray-100 font-sans relative z-0">
    <div class="max-w-6xl mx-auto pt-8">
        <p class="mb-6 text-black-lighter">
            Showing versions for {{ $count }} active projects and packages
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
                @foreach ($current as $project)
                    <ul class="flex list-reset p-4 border-t border-smoke">
                        <li class="w-2/6">
                            <a class="text-indigo-700 hover:text-indigo-900 no-underline text-md" href="{{ $project->github_url }}">
                                {{ $project->name }}
                            </a>
                        </li>

                        <li class="w-1/6 text-black-lightest">{{ $project->current_laravel_constraint }}</li>

                        <li class="w-1/6 text-black-lightest">{{ $project->current_laravel_version }}</li>

                        <li class="w-1/6 text-black-lightest">{{ $project->desired_laravel_version }}</li>

                        <li class="w-1/6 text-black-lightest">
                            <span class="text-green-700">CURRENT</span>
                        </li>

                        <li class="w-1/6">
                            <form action="{{ route('project.ignore', $project) }}" method="POST">
                                @method('PATCH')
                                @csrf
                                <input type="submit" value="Ignore" class="bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1 rounded cursor-pointer">
                            </form>
                        </li>
                    </ul>
                @endforeach
            </section>

            <section class="bg-white rounded-b-lg">
                @foreach ($behind as $project)
                    <ul class="flex list-reset p-4 border-t border-smoke">
                        <li class="w-2/6">
                            <a class="text-indigo-700 hover:text-indigo-900 no-underline text-md" href="{{ $project->github_url }}">
                                {{ $project->name }}
                            </a>
                        </li>

                        <li class="w-1/6 text-black-lightest">{{ $project->current_laravel_constraint }}</li>

                        <li class="w-1/6 text-black-lightest">{{ $project->current_laravel_version }}</li>

                        <li class="w-1/6 text-black-lightest">{{ $project->desired_laravel_version }}</li>

                        <li class="w-1/6 text-black-lightest">
                            <span class="font-bold text-red-700">BEHIND</span>
                        </li>

                        <li class="w-1/6">
                            <form action="{{ route('project.ignore', $project) }}" method="POST">
                                @method('PATCH')
                                @csrf
                                <input type="submit" value="Ignore" class="bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1 rounded cursor-pointer">
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
