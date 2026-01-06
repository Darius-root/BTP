<template>
    <Head title="Devises" />

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
                                    <CardTitle class="text-3xl">Devises</CardTitle>
                                    <CardDescription class="mt-1">
                                        Gérez les devises monétaires utilisées dans votre système
                                    </CardDescription>
                                </div>
                                <Link
                                    :href="route('devises.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors duration-200"
                                >
                                    <Plus class="w-5 h-5 mr-2" />
                                    Nouvelle devise
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
                                                placeholder="Rechercher par code, libellé ou symbole..."
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
                                    {{ filteredData.length }} devise(s) affichée(s)
                                    <template v-if="filteredData.length !== devises.length">
                                        sur {{ devises.length }}
                                    </template>
                                </div>

                                <!-- DataTable -->
                                <div class="overflow-x-auto border rounded-md">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead class="w-24">Code ISO</TableHead>
                                                <TableHead>Libellé</TableHead>
                                                <TableHead class="w-24">Symbole</TableHead>
                                                <TableHead class="text-right w-40">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <template v-if="filteredData.length">
                                                <TableRow
                                                    v-for="devise in filteredData"
                                                    :key="devise.id"
                                                    class="hover:bg-gray-50 dark:hover:bg-gray-800/50 group"
                                                >
                                                    <TableCell class="font-medium">
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800">
                                                                <Banknote class="w-4 h-4" />
                                                            </div>
                                                            <div>
                                                                <span class="font-mono text-sm font-bold text-gray-900 dark:text-gray-100">
                                                                    {{ devise.code }}
                                                                </span>
                                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                    ISO 4217
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                                            {{ devise.libelle }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                            Devise monétaire
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div class="flex items-center gap-2">
                                                            <span
                                                                v-if="devise.symbole"
                                                                class="px-3 py-1.5 bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 text-yellow-800 dark:text-yellow-300 rounded-lg text-sm font-bold border border-yellow-200 dark:border-yellow-800"
                                                            >
                                                                {{ devise.symbole }}
                                                            </span>
                                                            <span
                                                                v-else
                                                                class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded text-xs italic"
                                                            >
                                                                Non défini
                                                            </span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell class="text-right">
                                                        <div class="flex justify-end gap-2">
                                                            <Link
                                                                :href="route('devises.edit', devise.id)"
                                                                class="inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors"
                                                            >
                                                                <Edit class="w-4 h-4 mr-1" />
                                                                Modifier
                                                            </Link>
                                                            <button
                                                                @click="confirmDelete(devise)"
                                                                class="inline-flex items-center px-3 py-1.5 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
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
                                                            <Banknote class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                                                            <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                                Aucune devise trouvée
                                                            </p>
                                                            <p class="text-sm mt-1 text-gray-600 dark:text-gray-400">
                                                                {{ globalFilter ? 'Aucun résultat pour votre recherche' : 'Commencez par créer votre première devise' }}
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
import { Plus, Edit, Trash2, Banknote, Search, X } from 'lucide-vue-next'
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

const currentPageTitle = ref("Devises")

const props = defineProps({
    devises: {
        type: Array,
        required: true,
    }
})

const globalFilter = ref('')

const filteredData = computed(() => {
    if (!globalFilter.value) return props.devises

    const searchTerm = globalFilter.value.toLowerCase()
    return props.devises.filter(devise =>
        devise.code.toLowerCase().includes(searchTerm) ||
        devise.libelle.toLowerCase().includes(searchTerm) ||
        (devise.symbole && devise.symbole.toLowerCase().includes(searchTerm))
    )
})

const clearSearch = () => {
    globalFilter.value = ''
}

const confirmDelete = (devise) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la devise "${devise.libelle}" (${devise.code}) ? Cette action est irréversible.`)) {
        router.delete(route('devises.destroy', devise.id))
    }
}
</script>
