<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>@yield('title', 'BEMS')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Tailwind via Vite (si configuré) --}}
        @vite(['resources/css/app.css','resources/js/app.js'])

        {{-- Icônes Boxicons (nécessaire pour .bx ...) --}}
        <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    </head>
    <body class="bg-gray-50 text-gray-900 antialiased">
        <div class="min-h-screen flex">
            @auth
                @include('layouts.partials.sidebar')
            @endauth

            <main class="flex-1 min-h-screen">
                {{-- En-tête minimal (optionnel) --}}
                @auth
                    <div class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4">
                        <div class="text-sm text-gray-600">
                            Connecté comme <span class="font-medium text-gray-900">{{ auth()->user()->nom_complet }}</span>
                            <span class="text-gray-400">—</span>
                            <span class="uppercase text-xs text-gray-500">{{ auth()->user()->role }}</span>
                        </div>
                    </div>
                @endauth

                <div class="p-6">
                    @yield('contenu')
                </div>
            </main>
        </div>
    </body>
</html>
