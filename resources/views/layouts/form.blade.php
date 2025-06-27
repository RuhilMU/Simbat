<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="icon" type="image/png" href="{{ Storage::url(App\Models\Profile::first()->logo) }}">
        <title>{{ $title ?? 'SIMBAT' }}</title>
        @vite('resources/css/app.css')
        @vite('resources/js/app.js')
    </head>

    <body class="flex min-h-screen flex-col items-center justify-center">
        <img src="{{ Storage::url(App\Models\Profile::first()->logo) }}" class="w-18" alt="" />
        @session('success')
        @include('components.toast_success')
    @endsession
    @session('error')
        @include('components.toast_error')
    @endsession
        @yield('container')
    </body>
</html>
