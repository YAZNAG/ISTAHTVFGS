<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import { UserCircleIcon, KeyIcon } from '@heroicons/vue/24/outline';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});
</script>

<template>
    <Head title="Mon profil" />

    <AuthenticatedLayout>
        <section class="space-y-5">

            <!-- ═══ En-tête ═══ -->
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
                <h2 class="flex items-center gap-2 text-2xl font-bold text-istaht-navy">
                    <UserCircleIcon class="h-6 w-6" />
                    Mon profil
                </h2>
                <p class="mt-1 text-sm text-slate-500">Gérez vos informations personnelles et votre mot de passe.</p>
            </div>

            <!-- ═══ Informations personnelles ═══ -->
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
                <div class="mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <UserCircleIcon class="h-5 w-5 text-istaht-blue" />
                    <h3 class="font-bold text-istaht-navy">Informations personnelles</h3>
                </div>
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" class="max-w-xl" />
            </div>

            <!-- ═══ Mot de passe ═══ -->
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
                <div class="mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <KeyIcon class="h-5 w-5 text-istaht-blue" />
                    <h3 class="font-bold text-istaht-navy">Modifier le mot de passe</h3>
                </div>
                <UpdatePasswordForm class="max-w-xl" />
            </div>
        </section>
    </AuthenticatedLayout>
</template>
