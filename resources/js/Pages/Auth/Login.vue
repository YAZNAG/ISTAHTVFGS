<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { CubeIcon, EyeIcon, EyeSlashIcon, EnvelopeIcon, LockClosedIcon } from '@heroicons/vue/24/outline'

// Props Breeze (status pour messages type "Password reset link sent", etc.)
defineProps({
  canResetPassword: { type: Boolean, default: true },
  status: { type: String, default: '' },
})

// Inertia form (Breeze)
const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const showPassword = ref(false)
const hasLogo = ref(true)
const logoSrc = 'images/logo-istaht.png' // Placez un fichier public/logo-istaht.png

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Connexion" />

  <div class="min-h-screen bg-gradient-to-br from-indigo-700 via-blue-600 to-cyan-500 grid place-items-center px-4 py-10">
    <div class="w-full max-w-md">
      <!-- Brand -->
      <div class="flex flex-col items-center justify-center gap-3 mb-6 text-white">
        <div
          class="w-[120px] h-[120px] rounded-2xl overflow-hidden shadow-lg ring-1 ring-white/20 bg-white/10 backdrop-blur-md grid place-items-center"
        >
          <img
            v-if="hasLogo"
            :src="logoSrc"
            alt="ISTAHT"
            class="w-full h-full object-contain p-2"
            @error="hasLogo=false"
          />
          <CubeIcon v-else class="w-7 h-7 text-white" />
        </div>
        <div class="text-center">
          <div class="text-white/80 text-sm">Gestion des stocks</div>
        </div>
      </div>

      <!-- Card -->
      <div class="card p-6 shadow-xl">
        <h1 class="text-xl font-semibold">Connexion</h1>
        <p class="text-gray-500 mt-1">Accédez à votre espace administrateur</p>

        <!-- Message de statut (ex : lien de réinitialisation envoyé) -->
        <div v-if="status" class="mt-3 text-sm bg-red-300 rounded-lg p-3 text-red-700">
          {{ status }}
        </div>

        <form class="mt-5 space-y-4" @submit.prevent="submit">
          <!-- Email -->
          <div>
            <label for="email" class="text-sm text-gray-700">Email</label>
            <div class="mt-1 relative">
              <input
                id="email"
                class="input pl-10"
                type="email"
                v-model.trim="form.email"
                placeholder="admin@example.com"
                autocomplete="username"
                required
                autofocus
              />
              <EnvelopeIcon class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            </div>
            <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="text-sm text-gray-700">Mot de passe</label>
            <div class="mt-1 relative">
              <input
                id="password"
                class="input pl-10 pr-10"
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                placeholder="••••••••"
                autocomplete="current-password"
                required
              />
              <LockClosedIcon class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <button
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                @click="showPassword = !showPassword"
              >
                <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                <EyeSlashIcon v-else class="w-5 h-5" />
              </button>
            </div>
            <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
          </div>

          <!-- Remember me + Mot de passe oublié -->
          <div class="flex items-center justify-between pt-1">
            <label class="inline-flex items-center gap-2">
              <input
                name="remember"
                type="checkbox"
                v-model="form.remember"
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              />
              <span class="text-sm text-gray-600">Se souvenir de moi</span>
            </label>

            <Link
              v-if="canResetPassword"
              :href="route('password.request')"
              class="text-sm text-indigo-600 hover:text-indigo-500 underline"
            >
              Mot de passe oublié ?
            </Link>
          </div>

          <!-- Erreur d’auth générique éventuelle (côté Breeze : souvent sur 'email') -->
          <div v-if="!form.errors.password && form.errors.email && form.email && form.password" class="text-sm text-red-600">
            {{ form.errors.email }}
          </div>

          <!-- Bouton -->
          <button class="btn-primary w-full h-11 flex items-center justify-center gap-2" :disabled="form.processing">
            <span v-if="form.processing" class="inline-flex items-center gap-2">
              <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
              </svg>
              Connexion…
            </span>
            <span v-else>Se connecter</span>
          </button>
        </form>

        <div class="mt-4 text-xs text-gray-500 text-center">
          Accès réservé aux administrateurs.
        </div>
      </div>

      <!-- Footer -->
      <div class="mt-6 text-center text-white/80 text-xs">
        © {{ new Date().getFullYear() }} ISTAHT. Tous droits réservés.
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Petites définitions utilitaires basées sur Tailwind via @apply */
.card {
  @apply bg-white rounded-2xl border border-gray-200/60;
}
.input {
  @apply w-full h-11 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200/50 outline-none
         placeholder-gray-400 text-gray-900;
  @apply px-3; /* complétion du padding de base (le pl/pr est géré dans le template) */
}
.btn-primary {
  @apply rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700
         focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50;
}
</style>
