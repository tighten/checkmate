<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700|Open+Sans:400,700" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="css/main.css">

        <title>Version Check - Tighten</title>
    </head>

    <body>
        <div class="bg-white border-t-4 border-indigo relative z-10 shadow">
            <div class="p-2">
                <section class="max-w-lg mx-auto">
                    <div class="flex justify-between items-center">
                        <p class="flex items-center">
                            <span class="uppercase text-2xl leading-normal text-black-light font-semibold font-open-sans tracking-wide">Version Check</span>
                        </p>

                        <p class="italic font-thin leading-normal text-grey-blue-darkest">Catchy phrase I guess?</p>
                    </div>
                </section>
            </div>
        </div>

        <div class="bg-frost font-sans relative z-0">
            <div class="max-w-lg mx-auto pt-8">
                <p class="mb-6 text-black-lighter">Our projects n stuff?</p>

                <div class="rounded-lg shadow">
                    <ul class="bg-grey-blue-light flex list-reset p-4 rounded-t-lg border-grey border-b-2">
                        <li class="w-2/6 text-grey-darker font-semibold uppercase text-xs tracking-wide">Project name</li>

                        <li class="w-1/6 text-grey-darker font-semibold uppercase text-xs tracking-wide">Visibility</li>

                        <li class="w-1/6 text-grey-darker font-semibold uppercase text-xs tracking-wide">Fork</li>

                        <li class="w-1/6 text-grey-darker font-semibold uppercase text-xs tracking-wide">Poll</li>
                    </ul>

                    <section class="bg-white rounded-b-lg">
                        @foreach ($repos as $repo)
                            <ul class="flex list-reset p-4 border-t border-smoke">
                                <li class="w-2/6">
                                    <a class="text-indigo no-underline text-md" href="{{ $repo['html_url'] }}">
                                        {{ $repo['name'] }}
                                    </a>
                                </li>

                                <li class="w-1/6 text-black-lightest">{{ $repo['private'] === true ? 'Private' : 'Public' }}</li>

                                <li class="w-1/6 text-black-lightest">{{ $repo['fork'] === true ? 'Yes' : 'No' }}</li>

                                <li class="w-1/6 text-black-lightest">
                                    <label class="md:w-2/3 block text-gray-500 font-bold">
                                      <input class="mr-2 leading-tight" type="checkbox">
                                    </label>
                                </li>
                            </ul>
                        @endforeach
                    </section>
                    Count: {{ $repos->count() }}
                </div>
            </div>
            <br><br>
        </div>
    </body>
</html>
