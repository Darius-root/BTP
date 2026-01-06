<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { Card, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Plus } from 'lucide-vue-next'
import CorpsEtatDataTable from './components/CorpsEtatDataTable.vue'
import { createColumns } from './components/CorpsEtatColumns'

const props = defineProps({
    corpsEtats: Array,
    auth: Object,
    errors: Object
})

const currentPageTitle = 'Corps d\'État'

// Fonction de suppression
const confirmDelete = (corpsEtat) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer "${corpsEtat.intitule}" ?`)) {
        // Logique de suppression avec Inertia
        router.delete(route('corps-etat.destroy', corpsEtat.id))
    }
}

// Créer les colonnes avec la fonction de suppression
const columns = createColumns(confirmDelete)
</script>

<template>
    <Head :title="currentPageTitle" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <!-- Card principale -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Corps d'État</CardTitle>
                                    <CardDescription class="mt-1">
                                        Gérez les catégories de travaux (gros-œuvre, second-œuvre, etc.)
                                    </CardDescription>
                                </div>
                                <Link
                                    :href="route('corps-etat.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors duration-200"
                                >
                                    <Plus class="w-5 h-5 mr-2" />
                                    Nouveau corps d'état
                                </Link>
                            </div>
                        </CardHeader>

                        <!-- DataTable remplace tout le CardContent -->
                        <div class="px-0 mt-6">
                            <CorpsEtatDataTable
                                :columns="columns"
                                :data="corpsEtats"
                            />
                        </div>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>
