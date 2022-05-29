<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ (isset($title)?$title.' | ':'').config('app.name', 'Laravel') }}</title>
    <link rel="shortcut icon" href="{{ url('favicon.png') }}" type="image/png">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ url('css/app.css') }}">

    <!-- Scripts -->
    @livewireStyles
    @wireUiScripts
    <script src="{{ url('js/app.js') }}" defer></script>
</head>

<body>
    <x-dialog z-index="z-99999" blur="md" align="center" />
    <x-notifications z-index="z-99999" />
    <div class="font-sans antialiased text-gray-900">
        {{ $slot }}
    </div>
    @livewireScripts
</body>

</html>