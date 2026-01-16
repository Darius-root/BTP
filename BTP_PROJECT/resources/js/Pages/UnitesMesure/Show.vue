<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { ArrowLeft, SquarePen, Trash2, Ruler, Package } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import DeleteDialog from '@/components/DeleteDialog.vue'

const currentPageTitle = ref("Détails de l’unité de mesure")

// ⚠️ On récupère ici la prop côté Inertia : uniteMesure
const props = defineProps<{
    uniteMesure?: {
        id: number
        code: string
        libelle: string
        created_at: string
        updated_at: string
        materiaux: { id: number; code: string; nom: string }[]
    }
}>()

const deleteOpen = ref(false)

const formatDate = (date?: string) =>
    date
        ? new Date(date).toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        })
        : '-'
</script>

<template>

    <Head title="Détails de l’unité de mesure" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6" v-if="props.uniteMesure">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl flex items-center gap-2">
                                        <Ruler class="w-6 h-6 text-blue-600" />
                                        {{ props.uniteMesure?.libelle }}
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        Code : <span class="font-mono">{{ props.uniteMesure?.code }}</span>
                                    </CardDescription>
                                </div>

                                <Link :href="route('unites-mesure.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900">
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <Label>Libellé</Label>
                                    <p>{{ props.uniteMesure?.libelle }}</p>
                                </div>

                                <div>
                                    <Label>Code</Label>
                                    <p class="font-mono">{{ props.uniteMesure?.code }}</p>
                                </div>

                                <div>
                                    <Label>Date de création</Label>
                                    <p>{{ formatDate(props.uniteMesure?.created_at) }}</p>
                                </div>

                                <div>
                                    <Label>Dernière modification</Label>
                                    <p>{{ formatDate(props.uniteMesure?.updated_at) }}</p>
                                </div>
                            </div>

                            <!-- Matériaux -->
                            <div>
                                <h3 class="text-lg font-semibold flex items-center gap-2 mb-3">
                                    <Package class="w-5 h-5 text-green-600" />
                                    Matériaux liés
                                </h3>

                                <div v-if="props.uniteMesure?.materiaux?.length">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr>
                                                <th class="px-4 py-2 text-left">Code</th>
                                                <th class="px-4 py-2 text-left">Nom</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="mat in props.uniteMesure?.materiaux ?? []" :key="mat.id">
                                                <td class="px-4 py-2 font-mono text-blue-600">{{ mat.code }}</td>
                                                <td class="px-4 py-2">{{ mat.nom }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <p v-else class="text-sm text-gray-500">
                                    Aucun matériau lié à cette unité.
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end gap-3">
                                <Link v-if="props.uniteMesure?.id"
                                    :href="route('unites-mesure.edit', { uniteMesure: props.uniteMesure.id })"
                                    class="inline-flex items-center px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-md">
                                    <SquarePen class="w-4 h-4 mr-1" />
                                    Modifier
                                </Link>

                                <button @click="deleteOpen = true"
                                    class="inline-flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md">
                                    <Trash2 class="w-4 h-4 mr-1" />
                                    Supprimer
                                </button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- SUPPRESSION -->
            <DeleteDialog v-if="props.uniteMesure?.id" :open="deleteOpen" :item="props.uniteMesure" resource="unites-mesure"
                param="uniteMesure" :label="`l’unité ${props.uniteMesure?.libelle} (${props.uniteMesure?.code})`"
                @update:open="deleteOpen = $event" />
        </AdminLayout>
    </SidebarProvider>
</template>
