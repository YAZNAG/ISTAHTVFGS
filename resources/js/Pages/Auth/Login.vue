<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { CubeIcon, EyeIcon, EyeSlashIcon, EnvelopeIcon, LockClosedIcon } from '@heroicons/vue/24/outline'

defineProps({
  canResetPassword: { type: Boolean, default: true },
  status: { type: String, default: '' },
})

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const showPassword = ref(false)
const hasLogo = ref(true)
const logoSrc = '/images/logo-istaht.png'

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Connexion" />

  <div class="min-h-screen bg-istaht-mist px-4 py-8">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-6xl items-center justify-center">
      <div class="grid w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-panel lg:grid-cols-[0.95fr_1.05fr]">
        <section class="flex flex-col justify-between bg-istaht-navy p-8 text-white">
          <div>
            <Link href="/" class="inline-flex items-center gap-3">
              <span class="flex h-16 w-16 items-center justify-center rounded-lg bg-white shadow-soft">
                <img
                  v-if="hasLogo"
                  :src="logoSrc"
                  alt="ISTAHT"
                  class="h-14 w-14 object-contain"
                  @error="hasLogo = false"
                />
                <CubeIcon v-else class="h-8 w-8 text-istaht-blue" />
              </span>
              <span>
                <span class="block text-xl font-bold">ISTAHT Tanger</span>
                <span class="block text-sm font-medium text-cyan-100">Gestion administrative</span>
              </span>
            </Link>

            <div class="mt-12 max-w-md">
              <p class="text-sm font-bold uppercase text-istaht-turquoise">Espace sécurisé</p>
              <h1 class="mt-3 text-3xl font-bold leading-tight">Achats, stocks et demandes internes</h1>
              <p class="mt-4 text-sm leading-6 text-blue-100">
                Accès réservé aux utilisateurs autorisés de la plateforme ISPITSRK / ISTAHT.
              </p>
            </div>
          </div>

          <p class="mt-10 text-xs text-blue-100">
            © {{ new Date().getFullYear() }} ISTAHT. Tous droits réservés.
          </p>
        </section>

        <section class="p-6 sm:p-8 lg:p-10">
          <div class="mx-auto max-w-md">
            <p class="text-sm font-bold uppercase text-istaht-cyan">Connexion</p>
            <h2 class="mt-2 text-2xl font-bold text-istaht-navy">Accéder à l'application</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">
              Utilisez votre compte administrateur, magasinier, formateur ou validateur.
            </p>

            <div v-if="status" class="ui-alert ui-alert-info mt-5">
              {{ status }}
            </div>

            <form class="mt-6 space-y-5" @submit.prevent="submit">
              <div>
                <label for="email" class="ui-label">Email</label>
                <div class="relative mt-1.5">
                  <EnvelopeIcon class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                  <input
                    id="email"
                    v-model.trim="form.email"
                    class="ui-input pl-10"
                    type="email"
                    placeholder="admin@email.com"
                    autocomplete="username"
                    required
                    autofocus
                  />
                </div>
                <p v-if="form.errors.email" class="ui-error mt-1.5">{{ form.errors.email }}</p>
              </div>

              <div>
                <label for="password" class="ui-label">Mot de passe</label>
                <div class="relative mt-1.5">
                  <LockClosedIcon class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                  <input
                    id="password"
                    v-model="form.password"
                    class="ui-input pl-10 pr-11"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="********"
                    autocomplete="current-password"
                    required
                  />
                  <button
                    type="button"
                    class="absolute right-2 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-500 transition hover:bg-cyan-50 hover:text-istaht-blue"
                    :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                    @click="showPassword = !showPassword"
                  >
                    <EyeIcon v-if="!showPassword" class="h-5 w-5" />
                    <EyeSlashIcon v-else class="h-5 w-5" />
                  </button>
                </div>
                <p v-if="form.errors.password" class="ui-error mt-1.5">{{ form.errors.password }}</p>
              </div>

              <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <label class="inline-flex items-center gap-2">
                  <input
                    v-model="form.remember"
                    name="remember"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-istaht-blue focus:ring-istaht-cyan"
                  />
                  <span class="text-sm font-medium text-slate-600">Se souvenir de moi</span>
                </label>

                <Link
                  v-if="canResetPassword"
                  :href="route('password.request')"
                  class="text-sm font-semibold text-istaht-blue transition hover:text-istaht-cyan"
                >
                  Mot de passe oublié ?
                </Link>
              </div>

              <button
                type="submit"
                class="ui-button ui-button-primary w-full"
                :disabled="form.processing"
              >
                <span v-if="form.processing" class="inline-flex items-center gap-2">
                  <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                  Connexion...
                </span>
                <span v-else>Se connecter</span>
              </button>
            </form>

            <p class="mt-5 text-center text-xs font-medium text-slate-500">
              Accès réservé aux comptes autorisés.
            </p>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>
