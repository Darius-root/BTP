<template>
    <Head title="Bâtiments" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">
                                        Bâtiments du projet "{{ projet.nom }}"
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        Gérez les bâtiments liés à ce projet
                                    </CardDescription>
                                </div>

                                <Link :href="route('projets.batiments.create', projet.id)"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
                                    <Plus class="w-5 h-5 mr-2" /> Nouveau bâtiment
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
                                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                                            <Input id="searchFilter" v-model="globalFilter" type="text"
                                                placeholder="Nom, code..." class="pl-10 pr-10 w-full" autocomplete="off" />
                                            <button v-if="globalFilter" @click="clearSearch"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                <X class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Infos -->
                                <div class="text-sm text-gray-600">
                                    {{ filteredData.length }} bâtiment(s) affiché(s)
                                    <template v-if="filteredData.length !== batiments.data.length">
                                        sur {{ batiments.data.length }}
                                    </template>
                                </div>

                                <!-- Table -->
                                <div class="overflow-x-auto border rounded-md ">
                                    <Table>
                                        <TableHeader class="font-medium">
                                            <TableRow>
                                                <TableHead >Bâtiment</TableHead>
                                                <TableHead>Code</TableHead>
                                                <TableHead class="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>

                                        <TableBody>
                                            <template v-if="filteredData.length">
                                                <TableRow v-for="batiment in filteredData" :key="batiment.id" class="hover:bg-gray-50">
                                                    <TableCell class="font-medium">{{ batiment.nom }}</TableCell>
                                                    <TableCell>{{ batiment.code }}</TableCell>
                                                    <TableCell class="text-right">
                                                        <div class="flex justify-end gap-2">
                                                            <Link :href="route('batiments.show', batiment.id)"
                                                                class="inline-flex items-center px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors">
                                                                <Eye class="w-4 h-4 mr-1" /> Voir
                                                            </Link>

                                                            <Link :href="route('batiments.edit', batiment.id)"
                                                                class="inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                                                                <Edit class="w-4 h-4 mr-1" /> Modifier
                                                            </Link>

                                                            <button @click="confirmDelete(batiment)"
                                                                class="inline-flex items-center px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                                                <Trash2 class="w-4 h-4 mr-1" /> Supprimer
                                                            </button>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            </template>

                                            <template v-else>
                                                <TableRow>
                                                    <TableCell colspan="3" class="h-24 text-center">
                                                        <div class="py-8 text-gray-500">
                                                            <Building2 class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                                                            <p class="text-lg">Aucun bâtiment trouvé</p>
                                                            <p class="text-sm mt-1">
                                                                {{
                                                                    globalFilter
                                                                        ? 'Aucun résultat pour votre recherche'
                                                                        : 'Commencez par créer votre premier bâtiment'
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
import { Plus, Edit, Trash2, Search, X, Eye, Building2 } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const currentPageTitle = ref('Bâtiments')

const props = defineProps({
    batiments: { type: Object, required: true }, // pagination Inertia
    projet: { type: Object, required: true },   // projet courant
})

const globalFilter = ref('')

const filteredData = computed(() => {
    let data = props.batiments.data

    if (!globalFilter.value) return data

    const searchTerm = globalFilter.value.toLowerCase()

    return data.filter(b =>
        b.nom.toLowerCase().includes(searchTerm) ||
        b.code.toLowerCase().includes(searchTerm)
    )
})

const clearSearch = () => { globalFilter.value = '' }

const confirmDelete = (batiment) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le bâtiment "${batiment.nom}" ? Cette action est irréversible.`)) {
        router.delete(route('batiments.destroy', batiment.id))
    }
}
</script>
