<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'SIMBAT' }}</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" type="image/png" href="{{ Storage::url(App\Models\Profile::first()->logo) }}">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>

<body>
    @include('components.modal')
    @include('components.sidebar')
    <div class="sm:ml-64">
        @include('components.header')
    </div>
    <div class="p-4 sm:ml-64">
        @yield('container')
    </div>
    @session('success')
        @include('components.toast_success')
    @endsession
    @if(session('error') || $errors->any())
        @include('components.toast_error')
    @endif

</body>

</html>
