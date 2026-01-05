<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { Card, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Plus } from 'lucide-vue-next'
import BordereauDataTable from '@/components/bordereau/BordereauxDataTable.vue'
import { createColumns } from '@/components/bordereau/BordereauColumns'

const props = defineProps({
    bordereaux: Array,
    auth: Object,
    errors: Object
})

const currentPageTitle = 'Bordereaux'

const columns = createColumns()

const confirmDelete = (bordereau) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ?')) {
        // Logique de suppression avec Inertia
        router.delete(route('bordereaux.destroy', bordereau.id))
    }
}

</script>

<template>
    <Head :title="currentPageTitle" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :title="currentPageTitle" />
            <Card class="mb-4">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <div>
                        <CardTitle class="text-2xl font-bold tracking-tight">
                            Bordereaux
                        </CardTitle>
                        <CardDescription class="mt-1">
                            Gérez les catégories de travaux (gros-œuvre, second-œuvre, etc.)
                        </CardDescription>
                    </div>

                    <Link :href="route('bordereaux.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors duration-200">
                        <Plus class="w-5 h-5 mr-2" />
                        Importer un bordereau
                    </Link>
                </CardHeader>
            </Card>
            <BordereauDataTable :bordereaux="bordereaux" :columns="columns" @delete="confirmDelete" />
        </AdminLayout>
    </SidebarProvider>
</template>
