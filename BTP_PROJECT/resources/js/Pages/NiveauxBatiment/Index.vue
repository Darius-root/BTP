<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Plus } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog'

import DataTable from '@/components/niveaux-batiment/NiveauxBatimentDatatable.vue'
import { createColumns } from '@/components/niveaux-batiment/NiveauxBatimentColumns'

const currentPageTitle = ref('Niveaux de bâtiment')

/**
 * ⚠️ niveaux est un objet paginé Laravel
 */
const props = defineProps({
    niveaux: {
        type: Object,
        required: true,
    },
})

// Suppression
const showDeleteDialog = ref(false)
const niveauToDelete = ref(null)

const confirmDelete = (niveau) => {
    niveauToDelete.value = niveau
    showDeleteDialog.value = true
}

const handleDelete = () => {
    if (!niveauToDelete.value) return

    router.delete(route('niveaux-batiment.destroy', niveauToDelete.value.id), {
        onSuccess: () => {
            showDeleteDialog.value = false
            niveauToDelete.value = null
        },
    })
}

const columns = createColumns(confirmDelete)
</script>

<template>
    <Head title="Niveaux de bâtiment" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div class="rounded-2xl border  p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <!-- Header -->
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Niveaux de bâtiment</CardTitle>
                                    <CardDescription class="mt-1">
                                        Gérez les niveaux de bâtiment de votre organisation
                                    </CardDescription>
                                </div>

                                <Link
                                    :href="route('niveaux-batiment.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors"
                                >
                                    <Plus class="w-5 h-5 mr-2" />
                                    Nouveau niveau
                                </Link>
                            </div>
                        </CardHeader>

                        <!-- Content -->
                        <CardContent class="px-0">
                            <!-- ✅ CORRECTION ICI -->
                            <DataTable
                                :columns="columns"
                                :data="niveaux.data"
                            />
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Dialog suppression -->
            <AlertDialog v-model:open="showDeleteDialog">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Confirmer la suppression</AlertDialogTitle>
                        <AlertDialogDescription>
                            Êtes-vous sûr de vouloir supprimer le niveau de bâtiment
                            <span class="font-semibold">{{ niveauToDelete?.nom }}</span> ?
                            Cette action est irréversible.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Annuler</AlertDialogCancel>
                        <AlertDialogAction
                            class="bg-red-600 hover:bg-red-700 focus:ring-red-600"
                            @click="handleDelete"
                        >
                            Supprimer
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

        </AdminLayout>
    </SidebarProvider>
</template>
