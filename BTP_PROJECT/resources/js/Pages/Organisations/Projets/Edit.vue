<template>
    <Head title="Modifier projet" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Modifier le projet</CardTitle>
                                    <CardDescription class="mt-1">
                                        Mettez à jour les informations du projet
                                    </CardDescription>
                                </div>
                                <Link :href="route('projets.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="submit" class="space-y-6">
                                <!-- Nom -->
                                <div class="space-y-2">
                                    <Label for="nom">Nom du projet <span class="text-red-500">*</span></Label>
                                    <Input id="nom" v-model="form.nom" type="text"
                                        :class="{ 'border-red-300': form.errors.nom }" required />
                                    <p v-if="form.errors.nom" class="text-sm text-red-600">{{ form.errors.nom }}</p>
                                </div>

                                <!-- Code projet -->
                                <div class="space-y-2">
                                    <Label for="code_projet">Code projet <span class="text-red-500">*</span></Label>
                                    <Input id="code_projet" v-model="form.code_projet" type="text"
                                        :class="{ 'border-red-300': form.errors.code_projet }" required />
                                    <p v-if="form.errors.code_projet" class="text-sm text-red-600">{{ form.errors.code_projet }}</p>
                                </div>

                                <!-- Client & Organisation -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="client_id">Client <span class="text-red-500">*</span></Label>
                                        <select id="client_id" v-model="form.client_id"
                                            class="w-full border rounded-md p-2"
                                            :class="{ 'border-red-300': form.errors.client_id }" required>
                                            <option value="">Sélectionner un client</option>
                                            <option v-for="client in clients" :key="client.id" :value="client.id">
                                                {{ client.nom }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors.client_id" class="text-sm text-red-600">{{ form.errors.client_id }}</p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="organisation_id">Organisation <span class="text-red-500">*</span></Label>
                                        <select id="organisation_id" v-model="form.organisation_id"
                                            class="w-full border rounded-md p-2"
                                            :class="{ 'border-red-300': form.errors.organisation_id }" required>
                                            <option value="">Sélectionner une organisation</option>
                                            <option v-for="org in organisations" :key="org.id" :value="org.id">
                                                {{ org.raison_sociale }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors.organisation_id" class="text-sm text-red-600">{{ form.errors.organisation_id }}</p>
                                    </div>
                                </div>

                                <!-- Devise -->
                                <div class="space-y-2">
                                    <Label for="devise_id">Devise</Label>
                                    <select id="devise_id" v-model="form.devise_id"
                                        class="w-full border rounded-md p-2"
                                        :class="{ 'border-red-300': form.errors.devise_id }">
                                        <option value="">Sélectionner une devise</option>
                                        <option v-for="dev in devises" :key="dev.id" :value="dev.id">
                                            {{ dev.code }} - {{ dev.nom }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.devise_id" class="text-sm text-red-600">{{ form.errors.devise_id }}</p>
                                </div>

                                <!-- TVA & Budget -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="tva">TVA (%) <span class="text-red-500">*</span></Label>
                                        <Input id="tva" v-model="form.tva" type="number" min="0" max="100"
                                            :class="{ 'border-red-300': form.errors.tva }" required />
                                        <p v-if="form.errors.tva" class="text-sm text-red-600">{{ form.errors.tva }}</p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="budget_previsionnel">Budget prévisionnel</Label>
                                        <Input id="budget_previsionnel" v-model="form.budget_previsionnel" type="number"
                                            min="0"
                                            :class="{ 'border-red-300': form.errors.budget_previsionnel }" />
                                        <p v-if="form.errors.budget_previsionnel" class="text-sm text-red-600">{{ form.errors.budget_previsionnel }}</p>
                                    </div>
                                </div>

                                <!-- Localisation & Type -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="localisation">Localisation</Label>
                                        <Input id="localisation" v-model="form.localisation"
                                            :class="{ 'border-red-300': form.errors.localisation }" />
                                        <p v-if="form.errors.localisation" class="text-sm text-red-600">{{ form.errors.localisation }}</p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="type_projet">Type de projet</Label>
                                        <Input id="type_projet" v-model="form.type_projet"
                                            :class="{ 'border-red-300': form.errors.type_projet }" />
                                        <p v-if="form.errors.type_projet" class="text-sm text-red-600">{{ form.errors.type_projet }}</p>
                                    </div>
                                </div>

                                <!-- Résumé -->
                                <div class="space-y-2">
                                    <Label for="resume">Résumé</Label>
                                    <textarea id="resume" v-model="form.resume"
                                        class="w-full border rounded-md p-2 h-24"
                                        :class="{ 'border-red-300': form.errors.resume }"></textarea>
                                    <p v-if="form.errors.resume" class="text-sm text-red-600">{{ form.errors.resume }}</p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <Link :href="route('projets.index')" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                                        Annuler
                                    </Link>
                                    <Button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700">
                                        <span v-if="form.processing" class="flex items-center">
                                            <Loader2 class="w-4 h-4 mr-2 animate-spin" /> Mise à jour...
                                        </span>
                                        <span v-else class="flex items-center">
                                            <Save class="w-4 h-4 mr-2" /> Enregistrer
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
import { useForm, Link, Head } from '@inertiajs/vue3'
import { ArrowLeft, Loader2, Save } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

const props = defineProps({
    projet: Object,
    clients: Array,
    organisations: Array,
    devises: Array
})

const currentPageTitle = ref('Modifier projet')

const form = useForm({
    nom: props.projet.nom,
    code_projet: props.projet.code_projet,
    client_id: props.projet.client_id,
    organisation_id: props.projet.organisation_id,
    devise_id: props.projet.devise_id,
    tva: props.projet.tva,
    budget_previsionnel: props.projet.budget_previsionnel,
    localisation: props.projet.localisation,
    type_projet: props.projet.type_projet,
    resume: props.projet.resume
})

const submit = () => {
    form.put(route('projets.update', props.projet.id), { preserveScroll: true })
}
</script>
