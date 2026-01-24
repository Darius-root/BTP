<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { Shield, Eye } from 'lucide-vue-next'

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

import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'

const currentPageTitle = ref('Templates de Devis Quantitatifs & Estimatifs')

const props = defineProps({
    templates: {
        type: Array,
        required: true,
    },
})

/* Recherche globale */
const globalFilter = ref('')

/* Templates filtrés */
const filteredTemplates = computed(() => {
    if (!globalFilter.value) return props.templates

    const query = globalFilter.value.toLowerCase()

    return props.templates.filter(t =>
        t.code.toLowerCase().includes(query) ||
        t.intitule.toLowerCase().includes(query) ||
        t.batiment?.nom?.toLowerCase().includes(query) ||
        t.projet?.nom?.toLowerCase().includes(query) ||
        t.organisation?.nom?.toLowerCase().includes(query)
    )
})

/* Statistiques */
const systemTemplatesCount = computed(() =>
    props.templates.filter(t => t.organisation?.is_system).length
)

const customTemplatesCount = computed(() =>
    props.templates.filter(t => !t.organisation?.is_system).length
)

/* Navigation vers la page détail */
function viewTemplate(template) {
    // Vérifier la permission avant de naviguer
    if (template.permissions?.canView) {
        router.visit(route('templates-estimatif-qte.show', template.id))
    }
}
</script>

<template>
    <Head title="Templates de Devis Quantitatifs & Estimatifs" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">
                                        Templates de Devis Quantitatifs & Estimatifs
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        Liste des devis templates réutilisables
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0 space-y-6">
                            <!-- Recherche -->
                            <Input
                                v-model="globalFilter"
                                placeholder="Rechercher un template (code, intitulé, projet, bâtiment...)"
                                class="max-w-md"
                            />

                            <!-- Table des templates -->
                            <div class="rounded-lg border border-gray-200 overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 border-b border-gray-200">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-semibold">Code</th>
                                            <th class="px-4 py-3 text-left font-semibold">Intitulé</th>
                                            <th class="px-4 py-3 text-right font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="filteredTemplates.length === 0">
                                            <td
                                                colspan="3"
                                                class="px-4 py-8 text-center text-gray-500"
                                            >
                                                Aucun template trouvé
                                            </td>
                                        </tr>

                                        <tr
                                            v-for="template in filteredTemplates"
                                            :key="template.id"
                                            class="border-b border-gray-200 hover:bg-gray-50 transition-colors"
                                        >
                                            <td class="px-4 py-3">
                                                <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                                                    {{ template.code }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-3 font-medium">
                                                {{ template.intitule }}
                                            </td>

                                            <td class="px-4 py-3 text-right">
                                                <Button
                                                    v-if="template.permissions?.canView"
                                                    size="sm"
                                                    variant="outline"
                                                    @click="viewTemplate(template)"
                                                >
                                                    <Eye class="w-4 h-4 mr-1" />
                                                    Voir
                                                </Button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>
