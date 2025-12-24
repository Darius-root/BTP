<script setup lang="ts">
import InputError from '@/Components/InputError.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'

defineProps({
    status: {
        type: String,
    },
})

const form = useForm({
    email: '',
})

const submit = () => {
    form.post(route('password.email'))
}
</script>

<template>

    <Head title="Mot de passe oublié" />

    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">

            <!-- LOGO -->
            <div class="flex justify-center mb-8">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-xl bg-linear-to-br from-blue-600 to-indigo-600
                               flex items-center justify-center shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" />
                        </svg>
                    </div>

                    <div class="leading-tight">
                        <div class="text-xl font-extrabold text-gray-900 tracking-tight">
                            BTP<span class="text-blue-600"> Bénin</span>
                        </div>
                        <div class="text-xs text-gray-500 font-medium tracking-wide">
                            Devis · Projets · Estimation
                        </div>
                    </div>
                </div>
            </div>

            <!-- TITRE -->
            <div class="text-center mb-6">
                <h1 class="text-xl font-semibold text-gray-800">
                    Mot de passe oublié
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Entrez votre email pour recevoir un lien de réinitialisation
                </p>
            </div>

            <!-- STATUS -->
            <div v-if="status" class="mb-4 text-sm font-medium text-green-700 bg-green-50
                       border border-green-200 rounded-lg p-3">
                {{ status }}
            </div>

            <!-- FORMULAIRE -->
            <form @submit.prevent="submit" class="space-y-6">

                <!-- EMAIL (style identique Login) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>
                    <input v-model="form.email" type="email" class="w-full h-11 rounded-lg border border-gray-300 px-4 text-sm
                               focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="info@gmail.com"
                        required autofocus />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <!-- BOUTON -->
                <button type="submit" :disabled="form.processing" class="w-full py-3 text-white font-semibold rounded-lg
                           bg-linear-to-r from-blue-600 to-indigo-600
                           hover:from-blue-700 hover:to-indigo-700 transition
                           disabled:opacity-50">
                    Envoyer le lien de réinitialisation
                </button>
            </form>

            <!-- RETOUR LOGIN -->
            <div class="mt-6 text-center text-sm text-gray-600">
                <Link :href="route('login')" class="text-blue-600 font-medium hover:underline">
                    Retour à la connexion
                </Link>
            </div>

        </div>
    </div>
</template>
