<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ (isset($title)?$title.' | ':'').config('app.name', 'Aplikasi Ujian AKM').(auth()->check() &&
        auth()->user()->sekolah->name?' - '.auth()->user()->sekolah->name:'') }}</title>
    @php
    $logo = url('favicon.png');
    if (auth()->check() && auth()->user()->sekolah->logo &&
    Storage::disk('public')->exists('uploads/'.userFolder().'/'.auth()->user()->sekolah->logo)) {
    $logo = getUrl(auth()->user()->sekolah->logo);
    }
    @endphp
    <link rel="shortcut icon" href="{{ $logo }}" {!! auth()->check() && Storage::disk('public')->
    exists('uploads/'.userFolder().'/'.auth()->user()->sekolah->logo)?'type="'.Storage::disk('public')->mimeType('uploads/'.userFolder().'/'.auth()->user()->sekolah->logo).'"':'type="image/png"'
    !!}>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ url('css/app.css') }}">

    <!-- Scripts -->
    @livewireStyles
    @wireUiScripts
    @stack('scripts')
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