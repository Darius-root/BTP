<template>

    <Head title="Matériaux" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div
                    class="rounded-2xl border  p-5  lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Matériaux</CardTitle>
                                    <CardDescription class="mt-1">
                                        Liste des matériaux
                                    </CardDescription>
                                </div>

                                <Link :href="route('materiaux.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
                                    <Plus class="w-5 h-5 mr-2" />
                                    Nouveau matériau
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0 space-y-6">
                            <!-- DataTable -->
                            <MateriauxDataTable :data="materiaux" :columns="columns"
                                v-model:globalFilter="globalFilter" />
                        </CardContent>
                    </Card>
                </div>
            </div>

            <DeleteDialog :open="deleteOpen" :item="selectedMateriau" resource="materiaux"
                :label="selectedMateriau ? `le matériau ${selectedMateriau.nom} (${selectedMateriau.code})` : 'ce matériau'"
                @update:open="deleteOpen = $event" @deleted="selectedMateriau = null" />
        </AdminLayout>
    </SidebarProvider>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { Plus } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription
} from '@/components/ui/card'

import MateriauxDataTable from '@/components/materiaux/MateriauxDataTable.vue'
import { createMateriauxColumns } from '@/components/materiaux/MateriauxColumns'
import DeleteDialog from '@/components/DeleteDialog.vue'

const currentPageTitle = ref('Matériaux')

const props = defineProps<{
    materiaux: any[]
}>()

/* Recherche globale */
const globalFilter = ref('')

/* Suppression */
const deleteOpen = ref(false)
const selectedMateriau = ref<any | null>(null)

function onDelete(materiau: any) {
    selectedMateriau.value = materiau
    deleteOpen.value = true
}

const columns = createMateriauxColumns(onDelete)
</script>
