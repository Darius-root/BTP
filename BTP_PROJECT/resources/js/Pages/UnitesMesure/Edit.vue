<template>
    <Head :title="`Modifier Unité - ${unite.libelle}`" />

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
                                    <CardTitle class="text-3xl">Modifier l'unité de mesure</CardTitle>
                                    <CardDescription class="mt-1">
                                        Mettez à jour les informations de l'unité
                                        <span class="block text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">
                                            {{ unite.libelle }}
                                            <span v-if="unite.code" class="text-gray-500">
                                                ({{ unite.code }})
                                            </span>
                                        </span>
                                    </CardDescription>
                                </div>
                                <Link
                                    :href="route('unites-mesure.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors"
                                >
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="form.put(route('unites-mesure.update', unite.id))" class="space-y-6">

                                <!-- Code -->
                                <div class="space-y-2">
                                    <Label for="code" class="text-sm font-medium">
                                        Code <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="code"
                                        type="text"
                                        v-model="form.code"
                                        placeholder="Ex: KG, M, L..."
                                        required
                                        class="w-full"
                                        :class="{ 'border-red-300': form.errors.code }"
                                    />
                                    <p v-if="form.errors.code" class="text-sm text-red-600">
                                        {{ form.errors.code }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Code unique identifiant l'unité
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
                                        placeholder="Ex: Kilogramme, Mètre, Litre..."
                                        required
                                        class="w-full"
                                        :class="{ 'border-red-300': form.errors.libelle }"
                                    />
                                    <p v-if="form.errors.libelle" class="text-sm text-red-600">
                                        {{ form.errors.libelle }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Nom complet de l'unité de mesure
                                    </p>
                                </div>

                                <!-- Informations supplémentaires -->
                                <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <div class="text-gray-500 dark:text-gray-400">Date de création</div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ formatDate(unite.created_at) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-gray-500 dark:text-gray-400">Dernière mise à jour</div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ formatDate(unite.updated_at) }}
                                            </div>
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
                                        >
                                            <Trash2 class="w-4 h-4 inline mr-1" />
                                            Supprimer cette unité
                                        </button>
                                        <Link
                                            :href="route('unites-mesure.index')"
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
                                                Unité mise à jour avec succès !
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

const currentPageTitle = ref("Modifier Unité")

const props = defineProps({
    unite: {
        type: Object,
        required: true,
    }
})

// Initialiser le formulaire avec les données existantes
const form = useForm({
    code: props.unite.code || '',
    libelle: props.unite.libelle || ''
})

// Calculer le titre de la page
const pageTitle = computed(() => {
    return `Modifier Unité - ${props.unite.libelle}`
})

const confirmDelete = () => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer définitivement l'unité "${props.unite.libelle}" (${props.unite.code}) ? Cette action est irréversible.`)) {
        router.delete(route('unites-mesure.destroy', props.unite.id))
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
