<template>

    <Head title="Arrondissements" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <!-- Header -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Arrondissements</CardTitle>
                                    <CardDescription class="mt-1">
                                        Gérez les arrondissements des communes
                                    </CardDescription>
                                </div>

                                <Link :href="route('arrondissements.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
                                    <Plus class="w-5 h-5 mr-2" />
                                    Nouvel arrondissement
                                </Link>
                            </div>

                            <!-- Content -->
                            <CardContent class="px-0">
                                <DataTable :columns="columns" :data="arrondissements" />
                            </CardContent>

                        </CardHeader>

                        <CardContent class="px-0 space-y-6">
                            <!-- DataTable -->
                            <ArrondissementsDataTable :data="arrondissements" :columns="columns"
                                v-model:globalFilter="globalFilter" />
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Dialog suppression -->
            <DeleteDialog :open="deleteOpen" :item="selectedArrondissement" resource="arrondissements"
                :label="selectedArrondissement ? `l'arrondissement ${selectedArrondissement.libelle}` : ''"
                displayField="libelle" @update:open="deleteOpen = $event" @deleted="selectedArrondissement = null" />
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

import ArrondissementsDataTable from '@/components/arrondissement/ArrondissementsDataTable.vue'
import { createColumns } from '@/components/arrondissement/ArrondissementsColumns'
import DeleteDialog from '@/components/DeleteDialog.vue'

const currentPageTitle = ref('Arrondissements')

const props = defineProps<{
    arrondissements: any[]
}>()

/* Recherche globale */
const globalFilter = ref('')

/* Suppression */
const deleteOpen = ref(false)
const selectedArrondissement = ref<any | null>(null)

function onDelete(arrondissement: any) {
    selectedArrondissement.value = arrondissement
    deleteOpen.value = true
}

const columns = createColumns(onDelete)
</script>
