<template>

    <Head title="Détails du projet" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div class="rounded-2xl border  p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">{{ projet.nom }}</CardTitle>
                                    <CardDescription class="mt-1">
                                        Détails complets du projet
                                    </CardDescription>
                                </div>
                                <Link :href="route('projets.index')"
                                    class="inline-flex items-center transition-colors">
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0 space-y-6">
                            <!-- Informations principales -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm ">
                                <div>
                                    <p class="font-semibold">Code projet</p>
                                    <p>{{ projet.code_projet }}</p>
                                </div>

                                <div>
                                    <p class="font-semibold">Client</p>
                                    <p>{{ projet.client.nom }}</p>
                                </div>

                                <div>
                                    <p class="font-semibold">Organisation</p>
                                    <p>{{ projet.organisation.raison_sociale }}</p>
                                </div>

                                <div>
                                    <p class="font-semibold">Devise</p>
                                    <p>{{ projet.devise?.code ?? '—' }}</p>
                                </div>

                                <div>
                                    <p class="font-semibold">TVA</p>
                                    <p>{{ projet.tva }}%</p>
                                </div>

                                <div>
                                    <p class="font-semibold">Budget prévisionnel</p>
                                    <p>{{ projet.budget_previsionnel ?? '—' }}</p>
                                </div>

                                <div>
                                    <p class="font-semibold">Localisation</p>
                                    <p>{{ projet.localisation ?? '—' }}</p>
                                </div>

                                <div>
                                    <p class="font-semibold">Type de projet</p>
                                    <p>{{ projet.type_projet ?? '—' }}</p>
                                </div>
                            </div>

                            <!-- Résumé -->
                            <div class="space-y-2">
                                <p class="font-semibold">Résumé</p>
                                <p class="">{{ projet.resume ?? '—' }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                                <Link :href="route('projets.edit', projet.id)"
                                    class="inline-flex items-center px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors">
                                    <Edit class="w-4 h-4 mr-1" /> Modifier
                                </Link>
                                <button @click="confirmDelete"
                                    class="inline-flex items-center px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors">
                                    <Trash2 class="w-4 h-4 mr-1" /> Supprimer
                                </button>

                                <Link :href="route('projets.batiments.index', projet.id)"
                                    class="inline-flex items-center px-4 py-2 text-sm bg-gray-700 hover:bg-gray-800 text-white rounded-md transition-colors">
                                    <Building2 class="w-4 h-4 mr-1" />
                                    Voir les bâtiments
                                </Link>

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

const props = defineProps({
    projet: {
        type: Object,
        required: true
    }
})

const currentPageTitle = ref('Détails du projet')

const confirmDelete = () => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le projet "${props.projet.nom}" ? Cette action est irréversible.`)) {
        router.delete(route('projets.destroy', props.projet.id))
    }
}
</script>
