<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ (isset($title)?$title.' | ':'').config('app.name', 'Laravel') }}</title>
    @php
    $logo = url('favicon.png');
    if (auth()->check() && auth()->user()->sekolah->logo &&
    Storage::exists('app/public/uploads/'.userFolder().'/'.(auth()->user()->sekolah->logo))) {
    $logo = getUrl(auth()->user()->sekolah->logo);
    }
    @endphp
    <link rel="shortcut icon" href="{{ $logo }}" type="image/png">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ url('css/app.css') }}">

    <!-- Scripts -->
    @livewireStyles
    @wireUiScripts
    <script src="{{ url('js/app.js') }}" defer></script>
</head>

<body class="font-sans antialiased">
    <x-dialog z-index="z-99999" blur="md" align="center" />
    <x-notifications z-index="z-99999" />
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        <header class="bg-white shadow">
            <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</body>

</html>