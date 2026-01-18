<script setup>
import { ref, computed } from 'vue'
import { Head, router, Link } from '@inertiajs/vue3'
import { Plus, Shield, FileText, User, Eye, Copy } from 'lucide-vue-next'
import { usePermissions } from '@/composables/usePermissions'

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
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'

import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

import TemplatesDataTable from '@/components/templates/TemplatesDataTable.vue'
import { createTemplatesColumns } from '@/components/templates/TemplatesColumns'

const currentPageTitle = ref('Templates de Devis')

const props = defineProps({
    templates: {
        type: Array,
        required: true
    }
})

const { can } = usePermissions()

/* Recherche globale */
const globalFilter = ref('')

/* Filtre système uniquement */
const showSystemOnly = ref(false)

/* Templates filtrés */
const filteredTemplates = computed(() => {
    if (!showSystemOnly.value) {
        return props.templates
    }
    return props.templates.filter(t => t.organisation.is_system)
})

/* Statistiques */
const systemTemplatesCount = computed(() => 
    props.templates.filter(t => t.organisation.is_system).length
)

const customTemplatesCount = computed(() => 
    props.templates.filter(t => !t.organisation.is_system).length
)

/* Dialog détails */
const detailsOpen = ref(false)
const selectedTemplate = ref(null)

function onViewDetails(template) {
    selectedTemplate.value = template
    detailsOpen.value = true
}

function viewFullTemplate() {
    if (selectedTemplate.value) {
        router.visit(route('templates.show', selectedTemplate.value.id))
    }
}

function reuseTemplate() {
    if (selectedTemplate.value) {
        router.visit(route('templates.reuse', selectedTemplate.value.id))
    }
}

function toggleSystemOnly() {
    showSystemOnly.value = !showSystemOnly.value
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount) + ' MAD'
}

const columns = createTemplatesColumns(onViewDetails)
</script>

<template>
    <Head title="Templates de Devis" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Templates de Devis</CardTitle>
                                    <CardDescription class="mt-1">
                                        Liste des devis templates réutilisables
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0 space-y-6">
                            <!-- DataTable -->
                            <TemplatesDataTable 
                                :data="filteredTemplates" 
                                :columns="columns" 
                                v-model="globalFilter" 
                            />
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Dialog pour voir les détails -->
            <Dialog v-model:open="detailsOpen">
                <DialogContent class="max-w-3xl max-h-[80vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Détails du Template</DialogTitle>
                        <DialogDescription v-if="selectedTemplate">
                            {{ selectedTemplate.code }} - {{ selectedTemplate.intitule }}
                        </DialogDescription>
                    </DialogHeader>

                    <div v-if="selectedTemplate" class="space-y-4 mt-4">
                        <!-- Informations générales -->
                        <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Statut</p>
                                <Badge :variant="selectedTemplate.statut === 'valide' ? 'default' : 'secondary'">
                                    <strong>{{ selectedTemplate.statut }}</strong>
                                </Badge>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Date de création</p>
                                <p class="font-medium">{{ selectedTemplate.created_at }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Projet</p>
                                <p class="font-medium">{{ selectedTemplate.projet.nom }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Bâtiment</p>
                                <p class="font-medium">{{ selectedTemplate.batiment.nom }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Organisation</p>
                                <p class="font-medium flex items-center gap-2">
                                    {{ selectedTemplate.organisation.nom }}
                                    <Shield v-if="selectedTemplate.organisation.is_system" class="w-4 h-4 text-blue-600" />
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Composants</p>
                                <p class="font-medium">{{ selectedTemplate.nombre_composants }}</p>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="p-4 bg-blue-50 dark:bg-blue-950/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-semibold text-blue-900 dark:text-blue-100">Montant Total</span>
                                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ formatCurrency(selectedTemplate.total) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <DialogFooter class="mt-6 flex flex-wrap gap-2">
                        <Button variant="outline" @click="detailsOpen = false">
                            Fermer
                        </Button>

                        <!-- Voir le détail complet -->
                        <button
                            v-if="can('ORG_DEVIS_ESTIMATIF_VIEW')"
                            @click="viewFullTemplate"
                            class="inline-flex items-center px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                            <Eye class="w-4 h-4 mr-2" />
                            Voir le détail complet
                        </button>

                        <!-- Réutiliser -->
                        <button
                            v-if="can('ORG_DEVIS_ESTIMATIF_CREATE')"
                            @click="reuseTemplate"
                            class="inline-flex items-center px-4 py-2 text-sm text-green-600 hover:bg-green-50 rounded-md transition-colors">
                            <Copy class="w-4 h-4 mr-2" />
                            Réutiliser ce template
                        </button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AdminLayout>
    </SidebarProvider>
</template>