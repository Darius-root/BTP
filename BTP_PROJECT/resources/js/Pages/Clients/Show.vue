<template>
    <Head title="Détails du client" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div class="rounded-2xl border  p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">{{ client.nom }}</CardTitle>
                                    <CardDescription class="mt-1">
                                        Détails complets du client
                                    </CardDescription>
                                </div>

                                <Link :href="route('clients.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <div class="space-y-6">

                                <!-- Informations principales -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label>Nom</Label>
                                        <p class="text-gray-700">{{ client.nom }}</p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>Société</Label>
                                        <p class="text-gray-700">{{ client.societe || '—' }}</p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>Email</Label>
                                        <p class="text-gray-700">{{ client.email }}</p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>Téléphone</Label>
                                        <p class="text-gray-700">{{ client.telephone || '—' }}</p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>Adresse</Label>
                                        <p class="text-gray-700">{{ client.adresse || '—' }}</p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>Organisation</Label>
                                        <p class="text-gray-700">{{ client.organisation?.raison_sociale || '—' }}</p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                                    <Link :href="route('clients.edit', client.id)"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
                                        <Edit class="w-4 h-4 mr-2" /> Modifier
                                    </Link>

                                    <button @click="confirmDelete(client)"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
                                        <Trash2 class="w-4 h-4 mr-2" /> Supprimer
                                    </button>
                                </div>

                                <!-- Projets liés (optionnel) -->
                                <div v-if="client.projets?.length" class="mt-6">
                                    <Label>Projets associés</Label>
                                    <ul class="list-disc pl-5">
                                        <li v-for="projet in client.projets" :key="projet.id">
                                            {{ projet.nom }}
                                        </li>
                                    </ul>
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
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ArrowLeft, Edit, Trash2 } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Label } from '@/components/ui/label'

const props = defineProps({
    client: { type: Object, required: true }
})

const currentPageTitle = ref('Détails Client')

const confirmDelete = (client) => {
    if (
        confirm(`Êtes-vous sûr de vouloir supprimer le client "${client.nom}" ? Cette action est irréversible.`)
    ) {
        router.delete(route('clients.destroy', client.id))
    }
}
</script>
