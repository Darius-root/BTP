<script>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Components/layout/AdminLayout.vue'
import SidebarProvider from '@/Components/layout/SidebarProvider.vue'

export default {
    components: {
        AdminLayout,
        SidebarProvider,
        Head,
        Link,
    },
    props: {
        bordereaux: Object,
    },
    setup(props) {
        const filters = ref({
            annee: '',
            version: '',
        })

        const applyFilters = () => {
            router.get('/bordereaux', filters.value, {
                preserveState: true,
                preserveScroll: true,
            })
        }

        const resetFilters = () => {
            filters.value = {
                annee: '',
                version: '',
            }
            router.get('/bordereaux')
        }

        const deleteBordereau = (id) => {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce bordereau ?')) {
                router.delete(`/bordereaux/${id}`)
            }
        }

        return {
            filters,
            applyFilters,
            resetFilters,
            deleteBordereau,
        }
    },
}
</script>

<template>

    <Head title="Bordereaux" />

    <SidebarProvider>
        <AdminLayout>
            <div class="grid grid-cols-12 gap-4 md:gap-6">
                <div class="col-span-12">
                    <!-- Header -->
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                Bordereaux
                            </h1>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Gérez vos bordereaux importés
                            </p>
                        </div>
                        <Link href="/bordereaux/import"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Importer un bordereau
                        </Link>
                    </div>

                    <!-- Filters -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Année
                                </label>
                                <input v-model="filters.annee" type="number" placeholder="Ex: 2024"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Version
                                </label>
                                <input v-model="filters.version" type="text" placeholder="Ex: V1"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            <div class="flex items-end gap-2">
                                <button @click="applyFilters"
                                    class="flex-1 px-4 py-2 bg-gray-800 hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 text-white font-medium rounded-lg transition-colors duration-200">
                                    Filtrer
                                </button>
                                <button @click="resetFilters"
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors duration-200">
                                    Réinitialiser
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Libellé
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Année
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Version
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Lignes
                                        </th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="bordereau in bordereaux.data" :key="bordereau.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ bordereau.libelle }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ bordereau.annee }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ bordereau.version }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ bordereau.lignes_count }} ligne(s)
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <Link :href="`/bordereaux/${bordereau.id}`"
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                Voir
                                                </Link>
                                                <Link :href="`/bordereaux/${bordereau.id}/edit`"
                                                    class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                                Modifier
                                                </Link>
                                                <button @click="deleteBordereau(bordereau.id)"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    Supprimer
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="bordereaux.data.length === 0">
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="text-gray-500 dark:text-gray-400">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="mt-2 text-sm">Aucun bordereau trouvé</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="bordereaux.data.length > 0"
                            class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    Affichage de
                                    <span class="font-medium">{{ bordereaux.from }}</span>
                                    à
                                    <span class="font-medium">{{ bordereaux.to }}</span>
                                    sur
                                    <span class="font-medium">{{ bordereaux.total }}</span>
                                    résultats
                                </div>
                                <div class="flex gap-2">
                                    <Link v-if="bordereaux.prev_page_url" :href="bordereaux.prev_page_url"
                                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Précédent
                                    </Link>
                                    <Link v-if="bordereaux.next_page_url" :href="bordereaux.next_page_url"
                                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Suivant
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>
