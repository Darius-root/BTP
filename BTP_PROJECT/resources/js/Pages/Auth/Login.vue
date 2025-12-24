<template>

    <Head title="Connexion" />

    <div class="min-h-screen flex">
        <!-- Section gauche - Image -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-gray-900 overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('/images/login/login1.png');">
                <div class="absolute inset-0 bg-black/40"></div>
            </div>

            <div class="relative z-10 flex flex-col justify-center items-start p-16 text-white">
                <h1 class="text-5xl font-bold mb-6 leading-tight">
                    Bienvenue sur<br />BTP Bénin
                </h1>
                <p class="text-xl text-gray-200 max-w-md leading-relaxed">
                    Accédez à votre espace de gestion et pilotez vos projets de construction
                    en toute simplicité.
                </p>

                <div class="mt-12 flex space-x-4">
                    <div class="w-12 h-1 bg-blue-500"></div>
                    <div class="w-12 h-1 bg-orange-500"></div>
                    <div class="w-12 h-1 bg-yellow-500"></div>
                </div>
            </div>
        </div>

        <!-- Section droite - Formulaire -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <div class="flex justify-center mb-8">
                    <div class="flex items-center space-x-3">
                        <!-- Icône -->
                        <div class="w-12 h-12 rounded-xl bg-linear-to-br from-blue-600 to-indigo-600
                flex items-center justify-center shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" />
                            </svg>
                        </div>

                        <!-- Texte -->
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


                <!-- Titre -->
                <div class="text-center mb-8">
                    <!-- <h2 class="text-3xl font-bold text-gray-900 mb-2">
                        Connexion
                    </h2> -->
                    <p class="text-gray-600 font-bold">
                        Connectez-vous à votre compte
                    </p>
                </div>

                <!-- Message status -->
                <div v-if="status"
                    class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded-lg border border-green-200">
                    {{ status }}
                </div>

                <!-- Formulaire -->
                <form @submit.prevent="handleSubmit" class="space-y-6 animate-[fadeIn_0.6s_ease-out]">
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input v-model="form.email" type="email"
                            class="w-full h-11 rounded-lg border border-gray-300 px-4 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            placeholder="info@gmail.com" />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Mot de passe
                        </label>
                        <div class="relative">
                            <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
                                class="w-full h-11 rounded-lg border border-gray-300 px-4 pr-12 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                placeholder="Votre mot de passe" />
                            <button type="button" @click="togglePasswordVisibility"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                👁
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Remember + forgot -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center text-sm text-gray-600">
                            <input v-model="form.remember" type="checkbox"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <span class="ml-2">Se rappeler de moi</span>
                        </label>

                        <Link v-if="canResetPassword" :href="route('password.request')"
                            class="text-sm text-blue-600 hover:underline">
                            Mot de passe oublié ?
                        </Link>
                    </div>

                    <!-- Bouton -->
                    <button type="submit" :disabled="form.processing"
                        class="w-full py-3 text-white font-semibold rounded-lg bg-linear-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 transition">
                        Se connecter
                    </button>
                </form>

                <!-- Register -->
                <div class="mt-8 text-center text-sm text-gray-600">
                    Pas encore de compte ?
                    <Link :href="route('register')" class="text-blue-600 font-medium hover:underline">
                        S’inscrire
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>


<script setup lang="ts">
import { ref } from 'vue'
import FullScreenLayout from '@/Components/layout/FullScreenLayout.vue'
import CommonGridShape from '@/Components/common/CommonGridShape.vue'

import InputError from '@/Components/InputError.vue';

import { Head, Link, useForm } from '@inertiajs/vue3';
const showPassword = ref(false)
const keepLoggedIn = ref(false)
const form = useForm({
    email: '',
    password: '',
    remember: false,
})
defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value
}

const handleSubmit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>
