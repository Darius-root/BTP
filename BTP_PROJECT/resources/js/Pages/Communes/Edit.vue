<template>

    <Head :title="`Modifier Commune - ${commune.libelle}`" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <!-- Card du formulaire -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Modifier la commune</CardTitle>
                                    <CardDescription class="mt-1">
                                        Mettez à jour les informations de la commune
                                        <span class="block text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">
                                            {{ commune.libelle }} ({{ commune.code }})
                                        </span>
                                    </CardDescription>
                                </div>
                                <Link :href="route('communes.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>
                        <div v-if="hasRelations"
                            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <div class="flex">
                                <svg class="h-5 w-5 text-yellow-400 mt-0.5 flex-shrink-0" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                        Commune utilisée
                                    </h3>
                                    <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                        Cette commune contient des arrondissements ou est utilisée dans des collections
                                        de prix.
                                        La suppression n'est pas possible tant que ces relations existent.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <CardContent class="px-0">
                            <form @submit.prevent="submitForm" class="space-y-6">

                                <!-- Code (lecture seule) -->
                                <div class="space-y-2">
                                    <Label for="code" class="text-sm font-medium">
                                        Code
                                    </Label>
                                    <div class="relative">
                                        <Input id="code" type="text" v-model="form.code" disabled
                                            class="w-full bg-gray-50 dark:bg-gray-800 cursor-not-allowed font-mono text-lg" />
                                        <div
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <!-- <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg> -->
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        Le code ne peut pas être modifié après la création
                                    </p>
                                </div>

                                <!-- Libellé -->
                                <div class="space-y-2">
                                    <Label for="libelle" class="text-sm font-medium">
                                        Nom de la commune <span class="text-red-500">*</span>
                                    </Label>
                                    <Input id="libelle" type="text" v-model="form.libelle" placeholder="Ex: Cotonou"
                                        required class="w-full" :class="{ 'border-red-300': form.errors.libelle }"
                                        @input="form.errors.libelle = null" />
                                    <p v-if="form.errors.libelle" class="text-sm text-red-600">
                                        {{ form.errors.libelle }}
                                    </p>
                                    <p v-else class="text-xs text-gray-500">
                                        Nom complet de la commune (doit être unique)
                                    </p>
                                </div>

                                <!-- Informations supplémentaires -->
                                <div
                                    class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">
                                        Statistiques
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <div class="text-gray-500 dark:text-gray-400">Arrondissements</div>
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">
                                                {{ commune.arrondissements_count || 0 }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-gray-500 dark:text-gray-400">Collections de prix</div>
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">
                                                {{ commune.collections_prix_count || 0 }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-gray-500 dark:text-gray-400">Date de création</div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ formatDate(commune.created_at) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-gray-500 dark:text-gray-400">Dernière mise à jour</div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ formatDate(commune.updated_at) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Message d'avertissement si la commune est utilisée -->


                                <!-- Actions -->
                                <div
                                    class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center gap-4">
                                        <button v-if="!hasRelations" type="button" @click="confirmDelete"
                                            class="text-sm text-red-600 hover:text-red-800 dark:hover:text-red-400 transition-colors flex items-center gap-1">
                                            <Trash2 class="w-4 h-4" />
                                            Supprimer cette commune
                                        </button>
                                        <button v-else type="button" disabled
                                            class="text-sm text-gray-400 cursor-not-allowed flex items-center gap-1"
                                            title="Impossible de supprimer: commune utilisée">
                                            <Trash2 class="w-4 h-4" />
                                            Supprimer cette commune
                                        </button>
                                        <Link :href="route('communes.index')"
                                            class="text-sm text-gray-600 hover:text-gray-900 dark:hover:text-gray-300 transition-colors">
                                            Annuler
                                        </Link>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <Transition enter-active-class="transition-opacity duration-300"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition-opacity duration-300"
                                            leave-to-class="opacity-0">
                                            <p v-if="form.recentlySuccessful"
                                                class="text-sm text-green-600 dark:text-green-400">
                                                Commune mise à jour avec succès !
                                            </p>
                                        </Transition>
                                        <Button type="submit" :disabled="form.processing"
                                            class="bg-blue-600 hover:bg-blue-700">
                                            <span v-if="form.processing" class="flex items-center">
                                                <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                                                Mise à jour...
                                            </span>
                                            <span v-else class="flex items-center">
                                                <Save class="w-4 h-4 mr-2" />
                                                Mettre à jour
                                            </span>
                                        </Button>
                                    </div>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { ArrowLeft, Loader2, Save, Trash2 } from 'lucide-vue-next'
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
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

const currentPageTitle = ref("Modifier Commune")

const props = defineProps({
    commune: {
        type: Object,
        required: true,
    }
})

// Initialiser le formulaire avec les données existantes
const form = useForm({
    code: props.commune.code || '',
    libelle: props.commune.libelle || ''
})

// Vérifier si la commune a des relations qui empêchent la suppression
const hasRelations = computed(() => {
    return (props.commune.arrondissements_count > 0) || (props.commune.collections_prix_count > 0)
})

// Calculer le titre de la page
const pageTitle = computed(() => {
    return `Modifier Commune - ${props.commune.libelle}`
})

const confirmDelete = () => {
    if (hasRelations.value) {
        alert('Impossible de supprimer cette commune car elle contient des arrondissements ou est utilisée dans des collections de prix.')
        return
    }

    if (confirm(`Êtes-vous sûr de vouloir supprimer définitivement la commune "${props.commune.libelle}" (${props.commune.code}) ?\n\nCette action est irréversible.`)) {
        router.delete(route('communes.destroy', props.commune.id))
    }
}

const submitForm = () => {
    form.put(route('communes.update', props.commune.id))
}

const formatDate = (dateString) => {
    if (!dateString) return 'Non spécifié'
    try {
        const date = new Date(dateString)
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        })
    } catch {
        return 'Date invalide'
    }
}
</script>
