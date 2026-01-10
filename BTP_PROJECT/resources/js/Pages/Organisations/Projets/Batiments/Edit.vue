<template>
    <Head title="Modifier le bâtiment" />

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
                                        Modifier le bâtiment
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        Mettez à jour les informations du bâtiment
                                    </CardDescription>
                                </div>

                                <Link :href="route('batiments.index')"
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
                                    <Label for="nom">Nom <span class="text-red-500">*</span></Label>
                                    <Input
                                        id="nom"
                                        v-model="form.nom"
                                        type="text"
                                        :class="{ 'border-red-300': form.errors.nom }"
                                        required
                                    />
                                    <p v-if="form.errors.nom" class="text-sm text-red-600">
                                        {{ form.errors.nom }}
                                    </p>
                                </div>

                                <!-- Code -->
                                <div class="space-y-2">
                                    <Label for="code">Code <span class="text-red-500">*</span></Label>
                                    <Input
                                        id="code"
                                        v-model="form.code"
                                        type="text"
                                        :class="{ 'border-red-300': form.errors.code }"
                                        required
                                    />
                                    <p v-if="form.errors.code" class="text-sm text-red-600">
                                        {{ form.errors.code }}
                                    </p>
                                </div>

                                <!-- Localisation -->
                                <div class="space-y-2">
                                    <Label for="localisation">Localisation</Label>
                                    <Input
                                        id="localisation"
                                        v-model="form.localisation"
                                        type="text"
                                        :class="{ 'border-red-300': form.errors.localisation }"
                                    />
                                    <p v-if="form.errors.localisation" class="text-sm text-red-600">
                                        {{ form.errors.localisation }}
                                    </p>
                                </div>

                                <!-- Description -->
                                <div class="space-y-2">
                                    <Label for="description">Description</Label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        class="w-full border rounded-md p-2"
                                        :class="{ 'border-red-300': form.errors.description }"
                                        rows="4"
                                    ></textarea>
                                    <p v-if="form.errors.description" class="text-sm text-red-600">
                                        {{ form.errors.description }}
                                    </p>
                                </div>

                                <!-- Projet -->
                                <div class="space-y-2">
                                    <Label for="projet_id">Projet <span class="text-red-500">*</span></Label>
                                    <select
                                        id="projet_id"
                                        v-model="form.projet_id"
                                        class="w-full border rounded-md p-2"
                                        :class="{ 'border-red-300': form.errors.projet_id }"
                                        required
                                    >
                                        <option value="" disabled>Choisir un projet</option>
                                        <option v-for="projet in projets" :key="projet.id" :value="projet.id">
                                            {{ projet.nom }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.projet_id" class="text-sm text-red-600">
                                        {{ form.errors.projet_id }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <Link :href="route('batiments.index')"
                                        class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                                        Annuler
                                    </Link>

                                    <div class="flex items-center gap-3">
                                        <Transition
                                            enter-active-class="transition-opacity duration-300"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition-opacity duration-300"
                                            leave-to-class="opacity-0"
                                        >
                                            <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                                                Bâtiment mis à jour avec succès !
                                            </p>
                                        </Transition>

                                        <Button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700">
                                            <span v-if="form.processing" class="flex items-center">
                                                <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                                                Mise à jour...
                                            </span>
                                            <span v-else class="flex items-center">
                                                <Save class="w-4 h-4 mr-2" />
                                                Enregistrer
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
    batiment: { type: Object, required: true },
    projets: { type: Array, required: true },
})

const currentPageTitle = ref('Modifier Bâtiment')

const form = useForm({
    nom: props.batiment.nom,
    code: props.batiment.code,
    localisation: props.batiment.localisation,
    description: props.batiment.description,
    projet_id: props.batiment.projet_id,
})

const submit = () => {
    form.put(route('batiments.update', props.batiment.id), {
        forceFormData: true,
        preserveScroll: true,
    })
}
</script>
