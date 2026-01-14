<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { ArrowLeft, SquarePen, Trash2, MapPin, Building2 } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import DeleteDialog from '@/components/DeleteDialog.vue'

const currentPageTitle = ref("Détails de la commune")

const props = defineProps<{
    commune: {
        id: number
        code: string
        libelle: string
        created_at: string
        updated_at: string
        arrondissements_count: number
        collections_prix_count: number
        arrondissements: { id: number; code: string; libelle: string }[]
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

    <Head title="Détails de la commune" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl flex items-center gap-2">
                                        <MapPin class="w-6 h-6 text-blue-600" />
                                        {{ props.commune.libelle }}
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        Code : <span class="font-mono">{{ props.commune.code }}</span>
                                    </CardDescription>
                                </div>

                                <Link :href="route('communes.index')"
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
                                        <Label class="font-semibold">Libellé :</Label>
                                        <p>{{ props.commune.libelle }}</p>
                                    </div>

                                    <div>
                                        <Label class="font-semibold">Code :</Label>
                                        <p class="font-mono">{{ props.commune.code }}</p>
                                    </div>

                                    <div>
                                        <Label class="font-semibold">Arrondissements :</Label>
                                        <p>{{ props.commune.arrondissements_count }}</p>
                                    </div>

                                    <div>
                                        <Label class="font-semibold">Collections Prix :</Label>
                                        <p>{{ props.commune.collections_prix_count }}</p>
                                    </div>

                                    <div>
                                        <Label class="font-semibold">Date de création :</Label>
                                        <p>{{ formatDate(props.commune.created_at) }}</p>
                                    </div>

                                    <div>
                                        <Label class="font-semibold">Dernière modification :</Label>
                                        <p>{{ formatDate(props.commune.updated_at) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Liste des arrondissements liés -->
                            <div class="mt-8">
                                <h3 class="text-lg font-semibold mb-3 flex items-center gap-2">
                                    <Building2 class="w-5 h-5 text-green-600" />
                                    Arrondissements liés
                                </h3>
                                <div v-if="props.commune.arrondissements.length"
                                    class="border rounded-md overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Code
                                                </th>
                                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">
                                                    Libellé</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <tr v-for="arr in props.commune.arrondissements" :key="arr.id"
                                                class="hover:bg-gray-50">
                                                <td class="px-4 py-2 font-mono text-sm text-blue-600">{{ arr.code }}
                                                </td>
                                                <td class="px-4 py-2 text-sm">{{ arr.libelle }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p v-else class="text-sm text-gray-500">Aucun arrondissement lié à cette commune.</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                                <Link :href="route('communes.edit', { commune: props.commune.id })"
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
            <DeleteDialog :open="deleteOpen" :item="props.commune" resource="communes"
                :label="`la commune ${props.commune.libelle} (${props.commune.code})`"
                @update:open="deleteOpen = $event" />
        </AdminLayout>
    </SidebarProvider>
</template>
