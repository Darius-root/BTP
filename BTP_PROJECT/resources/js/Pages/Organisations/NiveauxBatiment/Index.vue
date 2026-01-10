<template>

    <Head title="Niveaux de bâtiment" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Niveaux de bâtiment</CardTitle>
                                    <CardDescription class="mt-1">
                                        {{ batiment ? `Niveaux du bâtiment "${batiment.nom}"` : 'Gérez les niveaux de bâtiment de votre organisation' }}
                                    </CardDescription>
                                </div>

                                <Link
                                    :href="batiment ? route('batiments.niveaux.create', batiment.id) : route('niveaux-batiment.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
                                    <Plus class="w-5 h-5 mr-2" />
                                    Nouveau niveau
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <div class="space-y-6">
                                <!-- Recherche -->
                                <div class="flex flex-col sm:flex-row gap-4 items-end">
                                    <div class="flex-1">
                                        <Label for="searchFilter">Rechercher</Label>
                                        <div class="relative mt-1">
                                            <Search
                                                class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                                            <Input id="searchFilter" v-model="globalFilter" type="text"
                                                placeholder="Code, nom, bâtiment..." class="pl-10 pr-10 w-full"
                                                autocomplete="off" />
                                            <button v-if="globalFilter" @click="clearSearch"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                <X class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Infos -->
                                <div class="text-sm text-gray-600">
                                    {{ filteredData.length }} niveau(x) affiché(s)
                                    <template v-if="filteredData.length !== niveaux.data.length">
                                        sur {{ niveaux.data.length }}
                                    </template>
                                </div>

                                <!-- Table -->
                                <div class="overflow-x-auto border rounded-md">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Code</TableHead>
                                                <TableHead>Nom</TableHead>
                                                <TableHead v-if="!batiment">Bâtiment</TableHead>
                                                <TableHead>Description</TableHead>
                                                <TableHead class="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>

                                        <TableBody>
                                            <template v-if="filteredData.length">
                                                <TableRow v-for="niveau in filteredData" :key="niveau.id"
                                                    class="hover:bg-gray-50">
                                                    <TableCell class="font-medium">{{ niveau.code }}</TableCell>
                                                    <TableCell>{{ niveau.nom }}</TableCell>
                                                    <TableCell v-if="!batiment">
                                                        {{ niveau.batiment?.nom ?? '—' }}
                                                    </TableCell>
                                                    <TableCell>
                                                        <span class="text-sm text-gray-600">
                                                            {{ niveau.description ? (niveau.description.length > 50 ?
                                                                niveau.description.substring(0, 50) + '...' :
                                                            niveau.description) : '—' }}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell class="text-right">
                                                        <div class="flex justify-end gap-2">
                                                            <!-- SHOW -->
                                                            <Link :href="route('niveaux-batiment.show', niveau.id)"
                                                                class="inline-flex items-center px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors">
                                                                <Eye class="w-4 h-4 mr-1" /> Voir
                                                            </Link>

                                                            <!-- EDIT -->
                                                            <Link :href="route('niveaux-batiment.edit', niveau.id)"
                                                                class="inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                                                                <Edit class="w-4 h-4 mr-1" /> Modifier
                                                            </Link>

                                                            <!-- DELETE -->
                                                            <button @click="confirmDelete(niveau)"
                                                                class="inline-flex items-center px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                                                <Trash2 class="w-4 h-4 mr-1" /> Supprimer
                                                            </button>
                                                        </div>

                                                    </TableCell>
                                                </TableRow>
                                            </template>

                                            <template v-else>
                                                <TableRow>
                                                    <TableCell :colspan="batiment ? 4 : 5" class="h-24 text-center">
                                                        <div class="py-8 text-gray-500">
                                                            <p class="text-lg">Aucun niveau trouvé</p>
                                                            <p class="text-sm mt-1">
                                                                {{
                                                                    globalFilter
                                                                        ? 'Aucun résultat pour votre recherche'
                                                                        : 'Commencez par créer votre premier niveau'
                                                                }}
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
import { Plus, Edit, Trash2, Search, X, Eye } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const currentPageTitle = ref('Niveaux de bâtiment')

const props = defineProps({
    niveaux: {
        type: Object,
        required: true,
    },
    batiment: {
        type: Object,
        default: null,
    },
    activeOrganisation: {
        type: Object,
        required: true,
    }
})

const globalFilter = ref('')

const filteredData = computed(() => {
    let data = props.niveaux.data

    if (!globalFilter.value) return data

    const searchTerm = globalFilter.value.toLowerCase()

    return data.filter(n =>
        n.code.toLowerCase().includes(searchTerm) ||
        n.nom.toLowerCase().includes(searchTerm) ||
        (n.batiment && n.batiment.nom.toLowerCase().includes(searchTerm)) ||
        (n.description && n.description.toLowerCase().includes(searchTerm))
    )
})

const clearSearch = () => { globalFilter.value = '' }

const confirmDelete = (niveau) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le niveau "${niveau.nom}" ? Cette action est irréversible.`)) {
        router.delete(route('niveaux-batiment.destroy', niveau.id))
    }
}
</script>