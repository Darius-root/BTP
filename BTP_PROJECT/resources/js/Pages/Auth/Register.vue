<template>

    <Head title="Inscription" />

    <div class="min-h-screen flex">
        <!-- Section gauche - Image -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-gray-900 overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('/images/login/login1.png');">
                <div class="absolute inset-0 bg-black/40"></div>
            </div>

            <div class="relative z-10 flex flex-col justify-center items-start p-16 text-white">
                <h1 class="text-5xl font-bold mb-6 leading-tight">
                    Rejoignez<br />BTP Bénin
                </h1>
                <p class="text-xl text-gray-200 max-w-md leading-relaxed">
                    Créez votre compte et commencez à gérer vos projets de construction
                    efficacement.
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
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600
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

                <!-- Titre -->
                <div class="text-center mb-8">
                    <p class="text-gray-600 font-bold">
                        Créez votre compte
                    </p>
                </div>

                <!-- Formulaire -->
                <form @submit.prevent="submit" class="space-y-6 animate-[fadeIn_0.6s_ease-out]">

                    <!-- Nom -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nom
                        </label>
                        <input v-model="form.name" type="text" class="w-full h-11 rounded-lg border border-gray-300 px-4 text-sm
                                   focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            placeholder="Votre nom" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input v-model="form.email" type="email" class="w-full h-11 rounded-lg border border-gray-300 px-4 text-sm
                                   focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            placeholder="info@gmail.com" />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Mot de passe -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Mot de passe
                        </label>
                        <input v-model="form.password" type="password" class="w-full h-11 rounded-lg border border-gray-300 px-4 text-sm
                                   focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            placeholder="Votre mot de passe" />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Confirmation -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Confirmation du mot de passe
                        </label>
                        <input v-model="form.password_confirmation" type="password" class="w-full h-11 rounded-lg border border-gray-300 px-4 text-sm
                                   focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            placeholder="Confirmez le mot de passe" />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Bouton -->
                    <button type="submit" :disabled="form.processing" class="w-full py-3 text-white font-semibold rounded-lg
                               bg-gradient-to-r from-blue-600 to-indigo-600
                               hover:from-blue-700 hover:to-indigo-700 transition">
                        Créer le compte
                    </button>
                </form>

                <!-- Lien login -->
                <div class="mt-8 text-center text-sm text-gray-600">
                    Déjà un compte ?
                    <Link :href="route('login')" class="text-blue-600 font-medium hover:underline">
                        Se connecter
                    </Link>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>
