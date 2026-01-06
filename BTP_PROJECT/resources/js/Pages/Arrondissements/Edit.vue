<template>
    <Head :title="`Modifier Arrondissement - ${arrondissement.libelle}`" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <!-- Card du formulaire -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Modifier l'arrondissement</CardTitle>
                                    <CardDescription class="mt-1">
                                        Mettez à jour les informations de l'arrondissement
                                        <span class="block text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">
                                            {{ arrondissement.libelle }} ({{ arrondissement.code }})
                                            <span v-if="arrondissement.commune" class="text-gray-500">
                                                - {{ arrondissement.commune.libelle }}
                                            </span>
                                        </span>
                                    </CardDescription>
                                </div>
                                <Link
                                    :href="route('arrondissements.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors"
                                >
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="form.put(route('arrondissements.update', arrondissement.id))" class="space-y-6">

                                <!-- Code -->
                                <div class="space-y-2">
                                    <Label for="code" class="text-sm font-medium">
                                        Code <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="code"
                                        type="text"
                                        v-model="form.code"
                                        placeholder="Ex: ARR001"
                                        required
                                        class="w-full"
                                        :class="{ 'border-red-300': form.errors.code }"
                                    />
                                    <p v-if="form.errors.code" class="text-sm text-red-600">
                                        {{ form.errors.code }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Code unique identifiant l'arrondissement
                                    </p>
                                </div>

                                <!-- Libellé -->
                                <div class="space-y-2">
                                    <Label for="libelle" class="text-sm font-medium">
                                        Libellé <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="libelle"
                                        type="text"
                                        v-model="form.libelle"
                                        placeholder="Ex: Arrondissement Centre"
                                        required
                                        class="w-full"
                                        :class="{ 'border-red-300': form.errors.libelle }"
                                    />
                                    <p v-if="form.errors.libelle" class="text-sm text-red-600">
                                        {{ form.errors.libelle }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Nom complet de l'arrondissement
                                    </p>
                                </div>

                                <!-- Commune -->
                                <div class="space-y-2">
                                    <Label for="commune_id" class="text-sm font-medium">
                                        Commune <span class="text-red-500">*</span>
                                    </Label>
                                    <select
                                        id="commune_id"
                                        v-model="form.commune_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                        :class="{ 'border-red-300': form.errors.commune_id }"
                                    >
                                        <option value="">Sélectionnez une commune</option>
                                        <option
                                            v-for="commune in communes"
                                            :key="commune.id"
                                            :value="commune.id"
                                            class="dark:bg-gray-800 dark:text-gray-100"
                                            :selected="commune.id === arrondissement.commune_id"
                                        >
                                            {{ commune.libelle }} ({{ commune.code }})
                                        </option>
                                    </select>
                                    <p v-if="form.errors.commune_id" class="text-sm text-red-600">
                                        {{ form.errors.commune_id }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Commune à laquelle appartient l'arrondissement
                                    </p>
                                </div>

                                <!-- Informations supplémentaires -->
                                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div v-if="arrondissement.commune">
                                            <div class="text-gray-500 dark:text-gray-400">Commune</div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ arrondissement.commune.libelle }}
                                                <span class="text-gray-500 text-xs">({{ arrondissement.commune.code }})</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-gray-500 dark:text-gray-400">Date de création</div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ formatDate(arrondissement.created_at) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-gray-500 dark:text-gray-400">Dernière mise à jour</div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ formatDate(arrondissement.updated_at) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="arrondissement.collections_prix_count" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <div class="text-gray-500 dark:text-gray-400">
                                            Utilisé dans {{ arrondissement.collections_prix_count }}
                                            {{ arrondissement.collections_prix_count === 1 ? 'collection de prix' : 'collections de prix' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center gap-4">
                                        <button
                                            type="button"
                                            @click="confirmDelete"
                                            class="text-sm text-red-600 hover:text-red-800 dark:hover:text-red-400 transition-colors"
                                            :disabled="arrondissement.collections_prix_count > 0"
                                        >
                                            <Trash2 class="w-4 h-4 inline mr-1" />
                                            {{ arrondissement.collections_prix_count > 0 ? 'Suppression impossible (utilisé)' : 'Supprimer cet arrondissement' }}
                                        </button>
                                        <Link
                                            :href="route('arrondissements.index')"
                                            class="text-sm text-gray-600 hover:text-gray-900 dark:hover:text-gray-300 transition-colors"
                                        >
                                            Annuler
                                        </Link>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <Transition
                                            enter-active-class="transition-opacity duration-300"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition-opacity duration-300"
                                            leave-to-class="opacity-0"
                                        >
                                            <p
                                                v-if="form.recentlySuccessful"
                                                class="text-sm text-green-600 dark:text-green-400"
                                            >
                                                Arrondissement mis à jour avec succès !
                                            </p>
                                        </Transition>
                                        <Button
                                            type="submit"
                                            :disabled="form.processing"
                                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 px-6"
                                        >
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

const currentPageTitle = ref("Modifier Arrondissement")

const props = defineProps({
    arrondissement: {
        type: Object,
        required: true,
    },
    communes: {
        type: Array,
        required: true,
    }
})

// Initialiser le formulaire avec les données existantes
const form = useForm({
    code: props.arrondissement.code || '',
    libelle: props.arrondissement.libelle || '',
    commune_id: props.arrondissement.commune_id || ''
})

// Calculer le titre de la page
const pageTitle = computed(() => {
    return `Modifier Arrondissement - ${props.arrondissement.libelle}`
})

const confirmDelete = () => {
    // Empêcher la suppression si utilisé dans des collections
    if (props.arrondissement.collections_prix_count > 0) {
        alert('Cet arrondissement ne peut pas être supprimé car il est utilisé dans des collections de prix.');
        return;
    }

    if (confirm(`Êtes-vous sûr de vouloir supprimer définitivement l'arrondissement "${props.arrondissement.libelle}" (${props.arrondissement.code}) ? Cette action est irréversible.`)) {
        router.delete(route('arrondissements.destroy', props.arrondissement.id))
    }
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
