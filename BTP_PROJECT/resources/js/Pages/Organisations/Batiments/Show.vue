<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ArrowLeft, Edit, Trash2, Building2 } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'

const props = defineProps({
    batiment: {
        type: Object,
        required: true
    }
})

const currentPageTitle = ref('Détails Bâtiment')

const confirmDelete = (batiment) => {
    if (
        confirm(
            `Êtes-vous sûr de vouloir supprimer le bâtiment "${batiment.nom}" ? Cette action est irréversible.`
        )
    ) {
        router.delete(route('batiments.destroy', batiment.id))
    }
}
</script>
<template>

    <Head title="Détails du bâtiment" />

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
                                        {{ batiment.nom }}
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        Informations détaillées du bâtiment
                                    </CardDescription>
                                </div>

                                <Link :href="route('batiments.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <div class="space-y-4 text-gray-700">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <Label class="font-semibold">Nom :</Label>
                                        <p>{{ batiment.nom }}</p>
                                    </div>
                                    <div>
                                        <Label class="font-semibold">Code :</Label>
                                        <p>{{ batiment.code }}</p>
                                    </div>
                                    <div>
                                        <Label class="font-semibold">Localisation :</Label>
                                        <p>{{ batiment.localisation ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <Label class="font-semibold">Projet :</Label>
                                        <p>{{ batiment.projet?.nom ?? '—' }}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <Label class="font-semibold">Description :</Label>
                                        <p>{{ batiment.description ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>







<!-- Actions -->

    <!-- ================= DEVIS ================= -->

    <!-- Créer devis (si aucun devis) -->
    <Link
        :href="route('batiments.devis.create', { batiment: batiment.id })"
        class="inline-flex items-center px-4 py-2 text-sm text-green-600 hover:bg-green-50 rounded-md transition-colors"
    >
        <Building2 class="w-4 h-4 mr-1" />
        Créer un devis
    </Link>

    <!-- Voir devis (si déjà créé) -->
    <Link
       
        :href="route('batiments.devis.show', { batiment: batiment.id, devi:1 })"
        class="inline-flex items-center px-4 py-2 text-sm text-purple-600 hover:bg-purple-50 rounded-md transition-colors"
    >
        <Building2 class="w-4 h-4 mr-1" />
        Voir le devis ESTIMATIF
    </Link>


   







                            <!-- Actions -->
                            <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                                

                                <!-- Modifier -->
                                <Link :href="route('batiments.edit', batiment.id)"
                                    class="inline-flex items-center px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                                    <Edit class="w-4 h-4 mr-1" />
                                    Modifier
                                </Link>

                                <!-- Supprimer -->
                                <button @click="confirmDelete(batiment)"
                                    class="inline-flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                    <Trash2 class="w-4 h-4 mr-1" />
                                    Supprimer
                                </button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>

