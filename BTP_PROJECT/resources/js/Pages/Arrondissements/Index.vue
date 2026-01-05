<template>
    <Head title="Arrondissements" />

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
                                    <CardTitle class="text-3xl">Arrondissements</CardTitle>
                                    <CardDescription class="mt-1">
                                        Gérez les arrondissements des communes
                                    </CardDescription>
                                </div>
                                <Link
                                    :href="route('arrondissements.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors duration-200"
                                >
                                    <Plus class="w-5 h-5 mr-2" />
                                    Nouvel arrondissement
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <div class="space-y-6">
                                <!-- Barre de recherche -->
                                <div class="flex flex-col sm:flex-row gap-4 items-end">
                                    <div class="flex-1">
                                        <Label for="searchFilter">Rechercher</Label>
                                        <div class="relative mt-1">
                                            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400 z-10" />
                                            <Input
                                                id="searchFilter"
                                                type="text"
                                                placeholder="Rechercher par code, libellé ou commune..."
                                                v-model="globalFilter"
                                                class="pl-10 pr-10 w-full"
                                                autocomplete="off"
                                            />
                                            <button
                                                v-if="globalFilter"
                                                @click="clearSearch"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 z-10"
                                            >
                                                <X class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info résultats -->
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ filteredData.length }} arrondissement(s) affiché(s)
                                    <template v-if="filteredData.length !== arrondissements.length">
                                        sur {{ arrondissements.length }}
                                    </template>
                                </div>

                                <!-- DataTable -->
                                <div class="overflow-x-auto border rounded-md">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead class="w-20">Code</TableHead>
                                                <TableHead>Libellé</TableHead>
                                                <TableHead>Commune</TableHead>
                                                <TableHead class="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <template v-if="filteredData.length">
                                                <TableRow
                                                    v-for="arrondissement in filteredData"
                                                    :key="arrondissement.id"
                                                    class="hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                                >
                                                    <TableCell class="font-medium">
                                                        <span class="font-mono text-sm text-blue-600 dark:text-blue-400">
                                                            {{ arrondissement.code }}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                                            {{ arrondissement.libelle }}
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div class="text-gray-700 dark:text-gray-300">
                                                            {{ arrondissement.commune.libelle }}
                                                        </div>
                                                    </TableCell>
                                                    <TableCell class="text-right">
                                                        <div class="flex justify-end gap-2">
                                                            <Link
                                                                :href="route('arrondissements.edit', arrondissement.id)"
                                                                class="inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors"
                                                            >
                                                                <Edit class="w-4 h-4 mr-1" />
                                                                Modifier
                                                            </Link>
                                                            <button
                                                                @click="confirmDelete(arrondissement)"
                                                                class="inline-flex items-center px-3 py-1.5 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md transition-colors"
                                                            >
                                                                <Trash2 class="w-4 h-4 mr-1" />
                                                                Supprimer
                                                            </button>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            </template>
                                            <template v-else>
                                                <TableRow>
                                                    <TableCell colspan="4" class="h-24 text-center">
                                                        <div class="text-center py-8 text-gray-500">
                                                            <MapPin class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                                                            <p class="text-lg">Aucun arrondissement trouvé</p>
                                                            <p class="text-sm mt-1">
                                                                {{ globalFilter ? 'Aucun résultat pour votre recherche' : 'Commencez par créer votre premier arrondissement' }}
                                                            </p>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            </template>
                                        </TableBody>
                                    </Table>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Plus, Edit, Trash2, MapPin, Search, X } from 'lucide-vue-next'
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const currentPageTitle = ref("Arrondissements")

const props = defineProps({
    arrondissements: {
        type: Array,
        required: true,
    }
})

const globalFilter = ref('')

// Filtrer les données localement
const filteredData = computed(() => {
    if (!globalFilter.value) return props.arrondissements

    const searchTerm = globalFilter.value.toLowerCase()
    return props.arrondissements.filter(arrondissement =>
        arrondissement.code.toLowerCase().includes(searchTerm) ||
        arrondissement.libelle.toLowerCase().includes(searchTerm) ||
        arrondissement.commune.libelle.toLowerCase().includes(searchTerm)
    )
})

const clearSearch = () => {
    globalFilter.value = ''
}

const confirmDelete = (arrondissement) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'arrondissement "${arrondissement.libelle}" ? Cette action est irréversible.`)) {
        router.delete(route('arrondissements.destroy', arrondissement.id))
    }
}
</script>
