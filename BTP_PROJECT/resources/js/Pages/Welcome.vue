<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Construmaster - Plateforme de Gestion BTP" />
    
    <div class="bg-gradient-to-br from-gray-50 to-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 min-h-screen">
        <!-- Navigation -->
        <header class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm shadow-sm sticky top-0 z-50">
            <div class="container mx-auto px-6 py-4">
                <div class="flex justify-between items-center">
                    <!-- Logo -->
                    <div class="flex items-center space-x-2">
                        <div class="h-10 w-10 bg-blue-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-xl">CM</span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Construmaster</h1>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Gestion BTP intelligente</p>
                        </div>
                    </div>

                    <!-- Navigation Links -->
                    <nav v-if="canLogin" class="flex items-center space-x-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition duration-300 shadow-md hover:shadow-lg"
                        >
                            Tableau de bord
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-5 py-2 text-gray-700 dark:text-gray-300 hover:text-blue-500 dark:hover:text-blue-600 font-medium transition duration-300"
                            >
                                Connexion
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition duration-300 shadow-md hover:shadow-lg"
                            >
                                Essai gratuit
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="container mx-auto px-6 py-16">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-12">
                <!-- Left Column - Text Content -->
                <div class="lg:w-1/2 space-y-8">
                    <div>
                        <span class="inline-block px-4 py-1 bg-blue-100 text-blue-600 rounded-full text-sm font-semibold mb-4">
                            Solution complète BTP
                        </span>
                        <h2 class="text-5xl font-bold text-gray-900 dark:text-white leading-tight">
                            Gérez vos projets BTP avec 
                            <span class="text-blue-500">efficacité</span>
                        </h2>
                        <p class="text-xl text-gray-600 dark:text-gray-300 mt-6">
                            Optimisez la gestion de vos chantiers, suivez vos équipes, 
                            et contrôlez vos coûts avec notre plateforme tout-en-un.
                        </p>
                    </div>

                    <!-- Features Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                        <div class="flex items-start space-x-3">
                            <div class="bg-blue-100 dark:bg-blue-900/30 p-2 rounded-lg">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-white">Gestion des chantiers</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Suivi en temps réel de l'avancement</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-3">
                            <div class="bg-blue-100 dark:bg-blue-900/30 p-2 rounded-lg">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-white">Contrôle des coûts</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Budgets et dépenses en un clin d'œil</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-3">
                            <div class="bg-blue-100 dark:bg-blue-900/30 p-2 rounded-lg">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-white">Planning équipes</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Organisation optimale des ressources</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-3">
                            <div class="bg-blue-100 dark:bg-blue-900/30 p-2 rounded-lg">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-white">Rapports détaillés</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Analyses et statistiques complètes</p>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 mt-10">
                        <Link
                            :href="canRegister ? route('register') : route('login')"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-4 rounded-lg font-bold text-lg transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 text-center"
                        >
                            Démarrer gratuitement
                        </Link>
                        <a href="#features" class="border-2 border-gray-300 dark:border-gray-600 hover:border-blue-500 text-gray-700 dark:text-gray-300 px-8 py-4 rounded-lg font-medium text-lg transition duration-300 text-center">
                            Voir les fonctionnalités
                        </a>
                    </div>

                    <!-- Trust Badges -->
                    <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Reconnu par plus de 500 entreprises BTP</p>
                        <div class="flex flex-wrap gap-8 items-center">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">500+</div>
                                <div class="text-gray-600 dark:text-gray-400">Clients</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">2,500+</div>
                                <div class="text-gray-600 dark:text-gray-400">Chantiers</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">99%</div>
                                <div class="text-gray-600 dark:text-gray-400">Satisfaction</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Dashboard Preview -->
                <div class="lg:w-1/2">
                    <div class="relative">
                        <!-- Dashboard Mockup -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                            <div class="bg-gray-100 dark:bg-gray-700 px-6 py-3 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                    <div class="ml-4 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tableau de bord - Chantier Paris
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <!-- Stats Cards -->
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                        <div class="text-sm text-blue-600 dark:text-blue-600">Budget utilisé</div>
                                        <div class="text-2xl font-bold text-gray-800 dark:text-white">75%</div>
                                        <div class="h-2 bg-blue-200 dark:bg-blue-800 rounded-full mt-2">
                                            <div class="h-full bg-blue-500 rounded-full w-3/4"></div>
                                        </div>
                                    </div>
                                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                                        <div class="text-sm text-green-600 dark:text-green-400">Avancement</div>
                                        <div class="text-2xl font-bold text-gray-800 dark:text-white">62%</div>
                                        <div class="h-2 bg-green-200 dark:bg-green-800 rounded-full mt-2">
                                            <div class="h-full bg-green-500 rounded-full w-3/5"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Task List -->
                                <div class="mb-6">
                                    <h4 class="font-bold text-gray-800 dark:text-white mb-3">Tâches aujourd'hui</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                                <span>Livraison matériaux</span>
                                            </div>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">09:00</span>
                                        </div>
                                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                <span>Inspection sécurité</span>
                                            </div>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">11:00</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Team Status -->
                                <div>
                                    <h4 class="font-bold text-gray-800 dark:text-white mb-3">Équipe sur site</h4>
                                    <div class="flex space-x-4">
                                        <div class="text-center">
                                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto">
                                                <span class="font-bold text-blue-600 dark:text-blue-600">8</span>
                                            </div>
                                            <span class="text-sm mt-1">Ouvriers</span>
                                        </div>
                                        <div class="text-center">
                                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto">
                                                <span class="font-bold text-green-600 dark:text-green-400">2</span>
                                            </div>
                                            <span class="text-sm mt-1">Chefs</span>
                                        </div>
                                        <div class="text-center">
                                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mx-auto">
                                                <span class="font-bold text-purple-600 dark:text-purple-400">1</span>
                                            </div>
                                            <span class="text-sm mt-1">Architecte</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Elements -->
                        <div class="absolute -bottom-6 -left-6 bg-blue-500 text-white p-4 rounded-xl shadow-xl">
                            <div class="text-sm">📅 Planning optimisé</div>
                        </div>
                        <div class="absolute -top-6 -right-6 bg-blue-500 text-white p-4 rounded-xl shadow-xl">
                            <div class="text-sm">📊 Rapports temps réel</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Features Section -->
        <section id="features" class="bg-white dark:bg-gray-800 py-20">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Une solution complète pour le BTP
                    </h2>
                    <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                        Découvrez toutes les fonctionnalités conçues spécifiquement pour les professionnels du bâtiment
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-8 rounded-2xl hover:shadow-xl transition duration-300">
                        <div class="bg-blue-100 dark:bg-blue-900/30 w-14 h-14 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Gestion de chantier</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Suivez l'avancement de vos chantiers en temps réel avec des tableaux de bord interactifs.
                        </p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700/50 p-8 rounded-2xl hover:shadow-xl transition duration-300">
                        <div class="bg-blue-100 dark:bg-blue-900/30 w-14 h-14 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Contrôle budgétaire</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Maîtrisez vos coûts avec des outils de suivi budgétaire et d'analyse des dépenses.
                        </p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700/50 p-8 rounded-2xl hover:shadow-xl transition duration-300">
                        <div class="bg-blue-100 dark:bg-blue-900/30 w-14 h-14 rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Documents & conformité</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Centralisez tous vos documents et assurez la conformité réglementaire.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-300 py-12">
            <div class="container mx-auto px-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-6 md:mb-0">
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="h-8 w-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold">CM</span>
                            </div>
                            <span class="text-xl font-bold text-white">Construmaster</span>
                        </div>
                        <p class="text-gray-400">© 2024 Tous droits réservés</p>
                    </div>
                    
                    <div class="flex space-x-6">
                        <a href="#" class="hover:text-blue-600 transition duration-300">Conditions d'utilisation</a>
                        <a href="#" class="hover:text-blue-600 transition duration-300">Confidentialité</a>
                        <a href="#" class="hover:text-blue-600 transition duration-300">Contact</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>