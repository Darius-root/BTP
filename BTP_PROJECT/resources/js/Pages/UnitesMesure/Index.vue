<template>

    <Head title="Unités de Mesure" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Unités de Mesure</CardTitle>
                                    <CardDescription class="mt-1">
                                        Liste des unités de mesure
                                    </CardDescription>
                                </div>

                                <Link :href="route('unites-mesure.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
                                    <Plus class="w-5 h-5 mr-2" />
                                    Nouvelle unité
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0 space-y-6">
                            <!-- DataTable -->
                            <UnitesDataTable :data="unites" :columns="columns" v-model:globalFilter="globalFilter" />
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- ✅ Dialog suppression -->
            <DeleteDialog :open="deleteOpen" :item="selectedUnite" resource="unites-mesure"
                :label="selectedUnite ? `l’unité ${selectedUnite.libelle} (${selectedUnite.code})` : 'cette unité'"
                @update:open="deleteOpen = $event" @deleted="selectedUnite = null" />
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

import UnitesDataTable from '@/components/unites-mesure/UnitesDataTable.vue'
import { createUnitesColumns } from '@/components/unites-mesure/UnitesColumns'
import DeleteDialog from '@/components/DeleteDialog.vue'

const currentPageTitle = ref('Unités de Mesure')

const props = defineProps<{
    unites: any[]
}>()

/* Recherche globale */
const globalFilter = ref('')

/* Suppression */
const deleteOpen = ref(false)
const selectedUnite = ref<any | null>(null)

function onDelete(unite: any) {
    selectedUnite.value = unite
    deleteOpen.value = true
}

const columns = createUnitesColumns(onDelete)
</script>
