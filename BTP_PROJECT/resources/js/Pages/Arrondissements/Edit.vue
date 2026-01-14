<template>

    <Head :title="`Modifier Arrondissement - ${arrondissement.libelle}`" />

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
                                    <CardTitle class="text-3xl">Modifier l'arrondissement</CardTitle>
                                    <CardDescription class="mt-1">
                                        Mettez à jour les informations de l'arrondissement
                                        <span class="block text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">
                                            {{ arrondissement.libelle }}
                                            <span v-if="arrondissement.commune" class="text-gray-500">
                                                - {{ arrondissement.commune.libelle }}
                                            </span>
                                        </span>
                                    </CardDescription>
                                </div>
                                <Link :href="route('arrondissements.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="form.put(route('arrondissements.update', arrondissement.id))"
                                class="space-y-6">

                                <!-- Libellé -->
                                <div class="space-y-2">
                                    <Label for="libelle" class="text-sm font-medium">
                                        Libellé <span class="text-red-500">*</span>
                                    </Label>
                                    <Input id="libelle" type="text" v-model="form.libelle"
                                        placeholder="Ex: Arrondissement Centre" required class="w-full"
                                        :class="{ 'border-red-300': form.errors.libelle }" />
                                    <p v-if="form.errors.libelle" class="text-sm text-red-600">
                                        {{ form.errors.libelle }}
                                    </p>
                                </div>

                                <!-- Commune -->
                                <div class="space-y-2">
                                    <Label for="commune_id" class="text-sm font-medium">
                                        Commune <span class="text-red-500">*</span>
                                    </Label>
                                    <select id="commune_id" v-model="form.commune_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                        :class="{ 'border-red-300': form.errors.commune_id }">
                                        <option value="">Sélectionnez une commune</option>
                                        <option v-for="commune in communes" :key="commune.id" :value="commune.id"
                                            class="dark:bg-gray-800 dark:text-gray-100">
                                            {{ commune.libelle }} ({{ commune.code }})
                                        </option>
                                    </select>
                                    <p v-if="form.errors.commune_id" class="text-sm text-red-600">
                                        {{ form.errors.commune_id }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div
                                    class="flex items-center justify-end pt-6 border-t border-gray-200 dark:border-gray-700">

                                    <div class="flex items-center gap-3">
                                        <Transition enter-active-class="transition-opacity duration-300"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition-opacity duration-300"
                                            leave-to-class="opacity-0">
                                            <p v-if="form.recentlySuccessful"
                                                class="text-sm text-green-600 dark:text-green-400">
                                                Arrondissement mis à jour avec succès !
                                            </p>
                                        </Transition>
                                        <Button type="submit" :disabled="form.processing"
                                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 px-6">
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
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

const currentPageTitle = ref("Modifier Arrondissement")

const props = defineProps({
    arrondissement: { type: Object, required: true },
    communes: { type: Array, required: true }
})

// Initialiser le formulaire avec les données existantes
const form = useForm({
    libelle: props.arrondissement.libelle || '',
    commune_id: props.arrondissement.commune_id || ''
})

const confirmDelete = () => {
    if (props.arrondissement.collections_prix_count > 0) {
        alert('Cet arrondissement ne peut pas être supprimé car il est utilisé dans des collections de prix.')
        return
    }

    if (confirm(`Êtes-vous sûr de vouloir supprimer définitivement l'arrondissement "${props.arrondissement.libelle}" ? Cette action est irréversible.`)) {
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
