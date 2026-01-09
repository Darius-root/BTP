<template>
    <Head title="Nouveau projet" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">
                                        Créer un projet
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        Remplissez les informations pour le nouveau projet.
                                        Le code sera généré automatiquement.
                                    </CardDescription>
                                </div>

                                <Link
                                    :href="route('projets.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors"
                                >
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <!-- Erreur globale -->
                            <div
                                v-if="form.errors.global"
                                class="mb-4 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700"
                            >
                                {{ form.errors.global }}
                            </div>

                            <form @submit.prevent="submit" class="space-y-6">
                                <!-- Nom -->
                                <div class="space-y-2">
                                    <Label for="nom">
                                        Nom du projet <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="nom"
                                        v-model="form.nom"
                                        type="text"
                                        required
                                        :class="{ 'border-red-300': form.errors.nom }"
                                    />
                                    <p v-if="form.errors.nom" class="text-sm text-red-600">
                                        {{ form.errors.nom }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Le code projet sera généré automatiquement à partir de ce nom
                                    </p>
                                </div>

                                <!-- Client -->
                                <div class="space-y-2">
                                    <Label for="client_id">
                                        Client <span class="text-red-500">*</span>
                                    </Label>
                                    <select
                                        id="client_id"
                                        v-model="form.client_id"
                                        class="w-full border rounded-md p-2"
                                        required
                                        :class="{ 'border-red-300': form.errors.client_id }"
                                    >
                                        <option value="">Sélectionner un client</option>
                                        <option
                                            v-for="client in clients"
                                            :key="client.id"
                                            :value="client.id"
                                        >
                                            {{ client.nom }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.client_id" class="text-sm text-red-600">
                                        {{ form.errors.client_id }}
                                    </p>
                                </div>

                                <!-- Devise -->
                                <div class="space-y-2">
                                    <Label for="devise_id">Devise</Label>
                                    <select
                                        id="devise_id"
                                        v-model="form.devise_id"
                                        class="w-full border rounded-md p-2"
                                        :class="{ 'border-red-300': form.errors.devise_id }"
                                    >
                                        <option value="">Sélectionner une devise</option>
                                        <option
                                            v-for="dev in devises"
                                            :key="dev.id"
                                            :value="dev.id"
                                        >
                                            {{ dev.code }} - {{ dev.libelle }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.devise_id" class="text-sm text-red-600">
                                        {{ form.errors.devise_id }}
                                    </p>
                                </div>

                                <!-- TVA & Budget -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="tva">
                                            TVA (%) <span class="text-red-500">*</span>
                                        </Label>
                                        <Input
                                            id="tva"
                                            v-model="form.tva"
                                            type="number"
                                            min="0"
                                            max="100"
                                            required
                                            :class="{ 'border-red-300': form.errors.tva }"
                                        />
                                        <p v-if="form.errors.tva" class="text-sm text-red-600">
                                            {{ form.errors.tva }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="budget_previsionnel">
                                            Budget prévisionnel
                                        </Label>
                                        <Input
                                            id="budget_previsionnel"
                                            v-model="form.budget_previsionnel"
                                            type="number"
                                            min="0"
                                            :class="{ 'border-red-300': form.errors.budget_previsionnel }"
                                        />
                                    </div>
                                </div>

                                <!-- Localisation & Type -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="localisation">Localisation</Label>
                                        <Input
                                            id="localisation"
                                            v-model="form.localisation"
                                            :class="{ 'border-red-300': form.errors.localisation }"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="type_projet">Type de projet</Label>
                                        <Input
                                            id="type_projet"
                                            v-model="form.type_projet"
                                            :class="{ 'border-red-300': form.errors.type_projet }"
                                        />
                                    </div>
                                </div>

                                <!-- Résumé -->
                                <div class="space-y-2">
                                    <Label for="resume">Résumé</Label>
                                    <textarea
                                        id="resume"
                                        v-model="form.resume"
                                        class="w-full border rounded-md p-2 h-24"
                                        :class="{ 'border-red-300': form.errors.resume }"
                                    />
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <Link
                                        :href="route('projets.index')"
                                        class="text-sm text-gray-600 hover:text-gray-900"
                                    >
                                        Annuler
                                    </Link>

                                    <Button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="bg-blue-600 hover:bg-blue-700"
                                    >
                                        <span v-if="form.processing" class="flex items-center">
                                            <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                                            Création...
                                        </span>
                                        <span v-else class="flex items-center">
                                            <Plus class="w-4 h-4 mr-2" />
                                            Créer le projet
                                        </span>
                                    </Button>
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
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, Loader2, Plus } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

defineProps({
    clients: Array,
    devises: Array,
})

const currentPageTitle = ref('Nouveau projet')

const form = useForm({
    nom: '',
    client_id: '',
    devise_id: '',
    tva: 0,
    budget_previsionnel: null,
    localisation: '',
    type_projet: '',
    resume: '',
})

const submit = () => {
    form.post(route('projets.store'), {
        preserveScroll: true,
    })
}
</script>
