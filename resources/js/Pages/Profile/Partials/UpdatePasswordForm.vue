<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);
const showCurrent = ref(false);
const showNew = ref(false);
const showConfirm = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <div>
        <p class="mb-4 text-sm text-slate-500">
            Utilisez un mot de passe long et unique pour sécuriser votre compte.
        </p>

        <form @submit.prevent="updatePassword" class="space-y-4">
            <!-- Mot de passe actuel -->
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Mot de passe actuel *</label>
                <div class="relative">
                    <input
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        :type="showCurrent ? 'text' : 'password'"
                        class="ui-input w-full pr-10"
                        autocomplete="current-password"
                    />
                    <button type="button" @click="showCurrent = !showCurrent" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600">
                        <component :is="showCurrent ? EyeSlashIcon : EyeIcon" class="h-5 w-5" />
                    </button>
                </div>
                <div v-if="form.errors.current_password" class="mt-1 text-sm font-semibold text-istaht-red">{{ form.errors.current_password }}</div>
            </div>

            <!-- Nouveau mot de passe -->
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Nouveau mot de passe *</label>
                <div class="relative">
                    <input
                        ref="passwordInput"
                        v-model="form.password"
                        :type="showNew ? 'text' : 'password'"
                        class="ui-input w-full pr-10"
                        autocomplete="new-password"
                    />
                    <button type="button" @click="showNew = !showNew" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600">
                        <component :is="showNew ? EyeSlashIcon : EyeIcon" class="h-5 w-5" />
                    </button>
                </div>
                <div v-if="form.errors.password" class="mt-1 text-sm font-semibold text-istaht-red">{{ form.errors.password }}</div>
            </div>

            <!-- Confirmation -->
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Confirmer le nouveau mot de passe *</label>
                <div class="relative">
                    <input
                        v-model="form.password_confirmation"
                        :type="showConfirm ? 'text' : 'password'"
                        class="ui-input w-full pr-10"
                        autocomplete="new-password"
                    />
                    <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600">
                        <component :is="showConfirm ? EyeSlashIcon : EyeIcon" class="h-5 w-5" />
                    </button>
                </div>
                <div v-if="form.errors.password_confirmation" class="mt-1 text-sm font-semibold text-istaht-red">{{ form.errors.password_confirmation }}</div>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit" :disabled="form.processing" class="ui-button ui-button-primary disabled:opacity-50">
                    {{ form.processing ? 'Mise à jour…' : 'Mettre à jour le mot de passe' }}
                </button>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm font-semibold text-istaht-green">Mot de passe modifié.</p>
                </Transition>
            </div>
        </form>
    </div>
</template>
