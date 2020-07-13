@extends('layouts.app')

@section('content')
<div class="bg-frost font-sans relative z-0">
    <div class="max-w-md mx-auto pt-8">
        <div class="rounded-lg shadow">
            <ul class="bg-grey-blue-light flex list-reset p-4 rounded-t-lg border-grey border-b-2">
                <li class="w-1/2 text-grey-darker font-semibold uppercase text-xs tracking-wide">Project / Package
                    name
                </li>
            </ul>

            <section class="bg-white rounded-b-lg">
                @foreach ($projects as $project)
                    <ul class="flex list-reset p-4 border-t border-smoke">
                        <li class="w-1/2">
                            <a class="text-indigo no-underline text-md" href="{{ $project->github_url }}">
                                {{ $project->name }}
                            </a>
                        </li>

                        <li class="w-1/2 text-right">
                            <form action="{{ route('project.unignore', $project) }}" method="POST">
                                @method('PATCH')
                                @csrf
                                <input type="submit" value="Un-Ignore" class="bg-indigo-muted hover:bg-indigo-light text-white px-3 py-1 rounded cursor-pointer">
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
