{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Association BEMS — Connexion</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { colors: { brand: { 500: "#0f172a"} } } } }
  </script>
</head>
<body class="min-h-screen bg-gray-50 antialiased text-gray-900">
  <div class="flex min-h-screen items-center justify-center py-10">
    <div class="w-full max-w-md">
      <!-- En-tête -->
      <div class="flex flex-col items-center mb-6">
        <div class="h-12 w-12 rounded-full bg-black/90 text-white flex items-center justify-center font-semibold">B</div>
        <h1 class="mt-3 text-lg font-semibold">Association BEMS</h1>
        <p class="text-sm text-gray-500">Portail – Connexion</p>
      </div>

      <!-- Carte -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @if ($errors->any())
          <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
          @csrf

          <!-- Email -->
          <label class="block text-sm font-medium text-gray-700">Adresse e-mail</label>
          <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <!-- Icône mail -->
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </span>
            <input
              type="email"
              name="email"
              value="{{ old('email') }}"
              placeholder="nom@bems.ch"
              required
              class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-10 pr-3 text-sm outline-none ring-0 focus:border-gray-400"
            />
          </div>

          <!-- Mot de passe -->
          <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
          <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <!-- Icône cadenas -->
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 10-8 0v4M5 11h14v8a2 2 0 01-2 2H7a2 2 0 01-2-2v-8z"/>
              </svg>
            </span>
            <input
              id="password"
              type="password"
              name="password"
              placeholder="•••••••"
              class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-10 pr-10 text-sm outline-none ring-0 focus:border-gray-400"
            />
            <button type="button" id="togglePwd"
              class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
              aria-label="Afficher/Masquer le mot de passe">
              <!-- Icône œil -->
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                <circle cx="12" cy="12" r="3" stroke-width="1.5" />
              </svg>
            </button>
          </div>

          <!-- Ligne options -->
          <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
              <input type="checkbox" name="remember" value="1" {{ old('remember', true) ? 'checked' : '' }}
                     class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-0">
              <span>Se souvenir de moi</span>
            </label>
          </div>

          <!-- Bouton -->
          <button type="submit"
                  class="mt-2 w-full rounded-xl bg-gray-900 py-2.5 text-sm font-semibold text-white hover:bg-black focus:outline-none">
            <span class="inline-flex items-center gap-2 justify-center">
              <!-- Icône flèche -->
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M13 5l7 7-7 7"/>
              </svg>
              Se connecter
            </span>
          </button>

          <p class="pt-2 text-center text-xs text-gray-500">
            En vous connectant, vous acceptez les conditions d'utilisation (prototype).
          </p>
        </form>
      </div>

      <!-- Pied de page -->
      <p class="mt-6 text-center text-xs text-gray-400">© 2025 BEMS – Prototype</p>
    </div>
  </div>
  <script>
    // Afficher / masquer le mot de passe
    document.getElementById('togglePwd')?.addEventListener('click', function () {
      const input = document.getElementById('password');
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
    });
  </script>
</body>
</html>
