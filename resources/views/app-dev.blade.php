<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>

    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            baseDomain: "{{ str_replace(['http://', 'https://', 'www.'], '', config('landlord.base_domain')) }}",
            menus: [],
            routes: [],
        };
    </script>
    
    {{-- Papa Leguas Assets (JS + CSS) --}}
    @vite(['vendor/callcocam/papa-leguas/resources/js/app.ts', 'vendor/callcocam/papa-leguas/resources/css/app.css'])
</head>

<body>
    <div id="app">
        <!-- loading spinner -->
        <div v-if="loading" class="fixed inset-0 flex items-center justify-center bg-white z-50">
            <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-32 w-32"> </div>
        </div>
    </div>
</body>

</html>