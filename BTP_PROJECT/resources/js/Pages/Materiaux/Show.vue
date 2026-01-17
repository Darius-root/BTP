<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { ArrowLeft, SquarePen, Trash2, Package } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import DeleteDialog from '@/components/DeleteDialog.vue'

const currentPageTitle = ref("Détails du matériau")

const props = defineProps<{
    materiau: {
        id: number
        code: string
        nom: string
        unite: { libelle: string }
        created_at: string
        updated_at: string
    }
}>()

const deleteOpen = ref(false)

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>

<template>

    <Head title="Détails du matériau" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div class="rounded-2xl border  p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl flex items-center gap-2">
                                        <Package class="w-6 h-6 text-green-600" />
                                        {{ materiau.nom }}
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        Code : <span class="font-mono">{{ materiau.code }}</span>
                                        <span class="ml-2">Unité : {{ materiau.unite?.libelle }}</span>
                                    </CardDescription>
                                </div>

                                <Link :href="route('materiaux.index')"
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
                                        <p>{{ materiau.nom }}</p>
                                    </div>

                                    <div>
                                        <Label class="font-semibold">Code :</Label>
                                        <p class="font-mono">{{ materiau.code }}</p>
                                    </div>

                                    <div>
                                        <Label class="font-semibold">Unité de mesure :</Label>
                                        <p>{{ materiau.unite?.libelle }}</p>
                                    </div>

                                    <div>
                                        <Label class="font-semibold">Date de création :</Label>
                                        <p>{{ formatDate(materiau.created_at) }}</p>
                                    </div>

                                    <div>
                                        <Label class="font-semibold">Dernière modification :</Label>
                                        <p>{{ formatDate(materiau.updated_at) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                                <Link :href="route('materiaux.edit', materiau.id)"
                                    class="inline-flex items-center px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                                    <SquarePen class="w-4 h-4 mr-1" />
                                    Modifier
                                </Link>

                                <button @click="deleteOpen = true"
                                    class="inline-flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                    <Trash2 class="w-4 h-4 mr-1" />
                                    Supprimer
                                </button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- ✅ Dialog suppression -->
            <DeleteDialog :open="deleteOpen" :item="materiau" resource="materiaux"
                :label="`le matériau ${materiau.nom} (${materiau.code})`" @update:open="deleteOpen = $event" />
        </AdminLayout>
    </SidebarProvider>
</template>
