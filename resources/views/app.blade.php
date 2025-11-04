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
            baseDomain: "{{ get_base_domain() }}",
            menus: @json($menus),
            routes: @json($routes),
        };
    </script>
    
    {{-- Papa Leguas Assets (JS + CSS) --}}
    @php
        $manifestPath = public_path('vendor/papa-leguas/manifest.json');
        $manifest = [];
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
        }        
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        $jsFile = $manifest['resources/js/app.ts']['file'] ?? null;
    @endphp
    
    @if($cssFile)
        <link rel="stylesheet" href="{{ asset('vendor/papa-leguas/' . $cssFile) }}">
    @endif
    
    @if($jsFile)
        <script src="{{ asset('vendor/papa-leguas/' . $jsFile) }}" defer></script>
    @endif
    @if (empty($manifest))
        @vite(['vendor/callcocam/papa-leguas/resources/css/app.css', 'vendor/callcocam/papa-leguas/resources/js/app.ts'])
    @endif
</head>

<body>
    <div id="app" class="w-full h-screen">
        <!-- loading spinner -->
        <div v-if="loading" class="fixed inset-0 flex items-center justify-center bg-white z-50">
            <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-32 w-32"> </div>
        </div>
    </div>
</body>

</html>