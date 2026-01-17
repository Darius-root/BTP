<template>

    <Head title="Nouvelle Commune" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <!-- Card du formulaire -->
                <div
                    class="rounded-2xl border  p-5  lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Créer une commune</CardTitle>
                                    <CardDescription class="mt-1">
                                        Remplissez les informations de la nouvelle commune
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
                            <form @submit.prevent="submitForm" class="space-y-6">

                                <!-- Libellé -->
                                <div class="space-y-2">
                                    <Label for="libelle" class="text-sm font-medium">
                                        Nom de la commune <span class="text-red-500">*</span>
                                    </Label>
                                    <Input id="libelle" type="text" v-model="form.libelle" placeholder="Ex: Cotonou"
                                        required class="w-full" :class="{ 'border-red-300': form.errors.libelle }"
                                        @input="handleLibelleChange" />
                                    <p v-if="form.errors.libelle" class="text-sm text-red-600">
                                        {{ form.errors.libelle }}
                                    </p>
                                    <p v-else class="text-xs text-gray-500">
                                        Nom complet de la commune (doit être unique)
                                    </p>
                                </div>

                                <!-- Code généré automatiquement (aperçu) -->
                               

                                <!-- Message d'information -->
                                

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <Link :href="route('communes.index')"
                                        class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                                        Annuler
                                    </Link>
                                    <div class="flex items-center gap-3">
                                        <Transition enter-active-class="transition-opacity duration-300"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition-opacity duration-300"
                                            leave-to-class="opacity-0">
                                            <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                                                Commune créée avec succès !
                                            </p>
                                        </Transition>
                                        <Button type="submit" :disabled="form.processing || !form.libelle"
                                            class="bg-blue-600 hover:bg-blue-700">
                                            <span v-if="form.processing">
                                                <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                                                Création...
                                            </span>
                                            <span v-else>
                                                Créer la commune
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
import { ref, watch } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { ArrowLeft, Loader2 } from 'lucide-vue-next'
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
import axios from 'axios'

const currentPageTitle = ref("Nouvelle Commune")
const previewCode = ref('')
let debounceTimeout = null

const form = useForm({
    libelle: ''
})

// Fonction pour prévisualiser le code
const previewCodeGeneration = async (libelle) => {
    if (!libelle || libelle.trim() === '') {
        previewCode.value = ''
        return
    }

    try {
        const response = await axios.post('/communes/preview-code', {
            libelle: libelle
        })
        previewCode.value = response.data.code
    } catch (error) {
        console.error('Erreur lors de la prévisualisation du code:', error)
    }
}

// Gérer le changement du libellé avec debounce
const handleLibelleChange = () => {
    form.errors.libelle = null

    // Annuler le timeout précédent
    if (debounceTimeout) {
        clearTimeout(debounceTimeout)
    }

    // Créer un nouveau timeout
    debounceTimeout = setTimeout(() => {
        previewCodeGeneration(form.libelle)
    }, 500) // Attendre 500ms après la dernière frappe
}

// Soumettre le formulaire
const submitForm = () => {
    form.post(route('communes.store'))
}
</script>
