<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <form @submit.prevent="form.patch(route('profile.update'))" class="space-y-4">
        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Nom *</label>
            <input v-model="form.name" type="text" class="ui-input w-full" required autofocus autocomplete="name" />
            <div v-if="form.errors.name" class="mt-1 text-sm font-semibold text-istaht-red">{{ form.errors.name }}</div>
        </div>

        <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Adresse email *</label>
            <input v-model="form.email" type="email" class="ui-input w-full" required autocomplete="username" />
            <div v-if="form.errors.email" class="mt-1 text-sm font-semibold text-istaht-red">{{ form.errors.email }}</div>
        </div>

        <div v-if="mustVerifyEmail && user.email_verified_at === null" class="rounded-lg border border-amber-100 bg-amber-50 px-3 py-2 text-sm text-amber-800">
            Votre adresse email n'est pas vérifiée.
            <Link :href="route('verification.send')" method="post" as="button" class="font-bold underline hover:text-amber-900">
                Cliquez ici pour renvoyer l'email de vérification.
            </Link>
            <p v-show="status === 'verification-link-sent'" class="mt-1 font-semibold text-istaht-green">
                Un nouveau lien de vérification a été envoyé à votre adresse email.
            </p>
        </div>

        <div class="flex items-center gap-3 pt-1">
            <button type="submit" :disabled="form.processing" class="ui-button ui-button-primary disabled:opacity-50">
                {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
            </button>
            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                <p v-if="form.recentlySuccessful" class="text-sm font-semibold text-istaht-green">Enregistré.</p>
            </Transition>
        </div>
    </form>
</template>
