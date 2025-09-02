<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>@yield('title', 'BEMS')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Tailwind via Vite (si configuré) --}}
        @vite(['resources/css/app.css','resources/js/app.js'])
        {{-- Icônes Boxicons --}}
        <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

        <style>
            /* Décale le contenu principal à droite de la sidebar */
            main {
                margin-left: 16rem; /* 16rem = 256px = largeur sidebar */
                width: calc(100% - 16rem);
                min-height: 100vh;
                display: block;
            }
        </style>
    </head>
    <body class="bg-gray-50 text-gray-900 antialiased">
        <div class="min-h-screen">
            @auth
                @include('layouts.partials.sidebar')
            @endauth

            <main>
                {{-- En-tête minimal (optionnel) --}}
                @auth
                    <div class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4">
                        <div class="text-sm text-gray-600">
                            Connecté comme
                            <span class="font-medium text-gray-900">{{ auth()->user()->nom_complet }}</span>
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
