<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">
		
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700|Open+Sans:400,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ mix('css/app.css') }}">
		
		<script src="{{ mix('js/app.js') }}" defer></script>

    <title>Checkmate - Tighten</title>
</head>

<body>

<header>
    @include('partials.header')
</header>

<main>
    @yield('content')
</main>

</body>
</html>
