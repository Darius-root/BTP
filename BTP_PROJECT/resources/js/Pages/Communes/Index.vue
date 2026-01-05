<template>
    <Head title="Communes" />

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
                                    <CardTitle class="text-3xl">Communes</CardTitle>
                                    <CardDescription class="mt-1">
                                        Gérez les communes de votre système
                                    </CardDescription>
                                </div>
                                <Link
                                    :href="route('communes.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors duration-200"
                                >
                                    <Plus class="w-5 h-5 mr-2" />
                                    Nouvelle commune
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
                                                placeholder="Rechercher par code ou libellé..."
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
                                    {{ communesToDisplay.length }} commune(s) affichée(s)
                                    <template v-if="communesToDisplay.length !== totalCommunes">
                                        sur {{ totalCommunes }}
                                    </template>
                                </div>

                                <!-- DataTable -->
                                <div class="overflow-x-auto border rounded-md">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead class="w-20">Code</TableHead>
                                                <TableHead>Libellé</TableHead>
                                                <TableHead class="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <template v-if="communesToDisplay.length">
                                                <TableRow
                                                    v-for="commune in communesToDisplay"
                                                    :key="commune.id"
                                                    class="hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                                >
                                                    <TableCell class="font-medium">
                                                        <span class="font-mono text-sm text-blue-600 dark:text-blue-400">
                                                            {{ commune.code }}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                                            {{ commune.libelle }}
                                                        </div>
                                                    </TableCell>
                                                    <TableCell class="text-right">
                                                        <div class="flex justify-end gap-2">
                                                            <Link
                                                                :href="route('communes.edit', commune.id)"
                                                                class="inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors"
                                                            >
                                                                <Edit class="w-4 h-4 mr-1" />
                                                                Modifier
                                                            </Link>
                                                            <button
                                                                @click="confirmDelete(commune)"
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
                                                    <TableCell colspan="3" class="h-24 text-center">
                                                        <div class="text-center py-8 text-gray-500">
                                                            <Building class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                                                            <p class="text-lg">Aucune commune trouvée</p>
                                                            <p class="text-sm mt-1">
                                                                {{ globalFilter ? 'Aucun résultat pour votre recherche' : 'Commencez par créer votre première commune' }}
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
import { Plus, Edit, Trash2, Building, Search, X } from 'lucide-vue-next'
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

const currentPageTitle = ref("Communes")

const props = defineProps({
    communes: {
        type: [Array, Object], // Accepte Array ou Object
        required: true,
    }
})

// Debug pour voir ce que vous recevez
console.log('Communes reçues:', props.communes)
console.log('Type:', typeof props.communes)
console.log('Est un tableau?', Array.isArray(props.communes))
console.log('A data property?', props.communes.data)

// Extraire les données selon le type reçu
const communesData = computed(() => {
    // Si c'est un objet paginateur Laravel
    if (props.communes && props.communes.data) {
        return props.communes.data
    }
    // Si c'est déjà un tableau
    return props.communes || []
})

// Nombre total de communes
const totalCommunes = computed(() => {
    // Si paginateur, utiliser total
    if (props.communes && props.communes.total !== undefined) {
        return props.communes.total
    }
    // Sinon, longueur du tableau
    return communesData.value.length
})

const globalFilter = ref('')

// Filtrer les données localement
const communesToDisplay = computed(() => {
    let data = communesData.value

    if (!globalFilter.value) return data

    const searchTerm = globalFilter.value.toLowerCase()
    return data.filter(commune =>
        commune.code.toLowerCase().includes(searchTerm) ||
        commune.libelle.toLowerCase().includes(searchTerm)
    )
})

const clearSearch = () => {
    globalFilter.value = ''
}

const confirmDelete = (commune) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la commune "${commune.libelle}" ? Cette action est irréversible.`)) {
        router.delete(route('communes.destroy', commune.id))
    }
}
</script>
