<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Plus, Edit, Trash2, Building2, Search, X } from 'lucide-vue-next'

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

const currentPageTitle = ref('Clients')

const props = defineProps({
    clients: {
        type: Object, // pagination Inertia
        required: true,
    }
})

const globalFilter = ref('')

const filteredData = computed(() => {
    if (!globalFilter.value) return props.clients.data

    const searchTerm = globalFilter.value.toLowerCase()

    return props.clients.data.filter(client =>
        client.nom.toLowerCase().includes(searchTerm) ||
        (client.email && client.email.toLowerCase().includes(searchTerm)) ||
        (client.organisation?.nom && client.organisation.nom.toLowerCase().includes(searchTerm))
    )
})

const clearSearch = () => {
    globalFilter.value = ''
}

const confirmDelete = (client) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le client "${client.nom}" ? Cette action est irréversible.`)) {
        router.delete(route('clients.destroy', client.id))
    }
}
</script>

<template>

    <Head title="Clients" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div class="rounded-2xl border  p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Clients</CardTitle>
                                    <CardDescription class="mt-1">
                                        Gérez les clients de votre système
                                    </CardDescription>
                                </div>

                                <Link :href="route('clients.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
                                    <Plus class="w-5 h-5 mr-2" />
                                    Nouveau client
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
                                                placeholder="Nom, email ou organisation..." class="pl-10 pr-10 w-full"
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
                                    {{ filteredData.length }} client(s) affiché(s)
                                    <template v-if="filteredData.length !== clients.data.length">
                                        sur {{ clients.data.length }}
                                    </template>
                                </div>

                                <!-- Table -->
                                <div class="overflow-x-auto border rounded-md">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Nom</TableHead>
                                                <TableHead>Email</TableHead>
                                                <TableHead>Téléphone</TableHead>
                                                <TableHead>Organisation</TableHead>
                                                <TableHead class="text-right">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>

                                        <TableBody>
                                            <template v-if="filteredData.length">
                                                <TableRow v-for="client in filteredData" :key="client.id"
                                                    class="hover:bg-gray-50">
                                                    <TableCell class="font-medium">
                                                        <div class="flex items-center gap-3">
                                                            <div
                                                                class="w-9 h-9 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                                                <Building2 class="w-5 h-5" />
                                                            </div>
                                                            <div>
                                                                <div class="font-semibold">{{ client.nom }}</div>
                                                                <div v-if="client.societe"
                                                                    class="text-xs text-gray-500">
                                                                    Société : {{ client.societe }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </TableCell>

                                                    <TableCell>{{ client.email }}</TableCell>
                                                    <TableCell>{{ client.telephone ?? '—' }}</TableCell>
                                                    <TableCell>{{ client.organisation?.nom ?? '—' }}
                                                    </TableCell>

                                                    <TableCell class="text-right">
                                                        <div class="flex justify-end gap-2">
                                                            <Link :href="route('clients.edit', client.id)"
                                                                class="inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                                                                <Edit class="w-4 h-4 mr-1" /> Modifier
                                                            </Link>

                                                            <button @click="confirmDelete(client)"
                                                                class="inline-flex items-center px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                                                <Trash2 class="w-4 h-4 mr-1" /> Supprimer
                                                            </button>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            </template>

                                            <template v-else>
                                                <TableRow>
                                                    <TableCell colspan="5" class="h-24 text-center">
                                                        <div class="py-8 text-gray-500">
                                                            <Building2 class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                                                            <p class="text-lg">Aucun client trouvé</p>
                                                            <p class="text-sm mt-1">
                                                                {{
                                                                    globalFilter
                                                                        ? 'Aucun résultat pour votre recherche'
                                                                        : 'Commencez par créer votre premier client'
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
