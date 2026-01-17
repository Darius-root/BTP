<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Plus } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { Card, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'

import CorpsEtatDataTable from '@/components/corps-etats/CorpsEtatDataTable.vue'
import { createColumns } from '@/components/corps-etats/CorpsEtatColumns'
import DeleteDialog from '@/components/DeleteDialog.vue'

const props = defineProps({
    corpsEtats: Array,
    auth: Object,
    errors: Object
})

const currentPageTitle = 'Corps d\'État'

// --- États pour la suppression ---
const deleteOpen = ref(false)
const selectedCorpsEtat = ref < any | null > (null)

// Fonction appelée depuis les colonnes
const confirmDelete = (corpsEtat: any) => {
    selectedCorpsEtat.value = corpsEtat
    deleteOpen.value = true
}

// Colonnes avec bouton Supprimer
const columns = createColumns(confirmDelete)
</script>

<template>

    <Head :title="currentPageTitle" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <!-- Card principale -->
                <div
                    class="rounded-2xl border  p-5  lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Corps d'État</CardTitle>
                                    <CardDescription class="mt-1">
                                        Gérez les catégories de travaux (gros-œuvre, second-œuvre, etc.)
                                    </CardDescription>
                                </div>
                                <Link :href="route('corps-etat.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors duration-200">
                                    <Plus class="w-5 h-5 mr-2" />
                                    Nouveau corps d'état
                                </Link>
                            </div>
                        </CardHeader>

                        <!-- DataTable -->
                        <div class="px-0 mt-6">
                            <CorpsEtatDataTable :columns="columns" :data="corpsEtats" />
                        </div>
                    </Card>
                </div>
            </div>

            <!-- Dialog suppression -->
            <DeleteDialog :open="deleteOpen" :item="selectedCorpsEtat" resource="corps-etat"
                :label="selectedCorpsEtat ? `le corps d'état ${selectedCorpsEtat.intitule}` : 'ce corps d\'état'"
                @update:open="deleteOpen = $event" @deleted="selectedCorpsEtat = null" />
        </AdminLayout>
    </SidebarProvider>
</template>
