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
    projet: { type: Object, required: true }
})

const currentPageTitle = ref(`Créer un bâtiment pour ${props.projet.nom}`)

const form = useForm({
    nom: '',
    code: '',
    localisation: '',
    description: '',
    projet_id: props.projet.id // assignation automatique
})

const submit = () => {
    form.post(route('batiments.store'), {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => console.log('Erreurs validation:', errors),
        onSuccess: () => console.log('Bâtiment créé avec succès')
    })
}
</script>

<template>

    <Head :title="currentPageTitle" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div class="rounded-2xl border  p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">
                                        Créer un bâtiment
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        Ajoutez un bâtiment au projet "{{ props.projet.nom }}"
                                    </CardDescription>
                                </div>

                                <Link :href="route('projets.batiments.index', props.projet.id)"
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
                                    <Input id="nom" v-model="form.nom" type="text"
                                        :class="{ 'border-red-300': form.errors.nom }" required />
                                    <p v-if="form.errors.nom" class="text-sm text-red-600">{{ form.errors.nom }}</p>
                                </div>

                                <!-- Code -->
                                <div class="space-y-2">
                                    <Label for="code">Code <span class="text-red-500">*</span></Label>
                                    <Input id="code" v-model="form.code" type="text"
                                        :class="{ 'border-red-300': form.errors.code }" required />
                                    <p v-if="form.errors.code" class="text-sm text-red-600">{{ form.errors.code }}</p>
                                </div>

                                <!-- Localisation -->
                                <div class="space-y-2">
                                    <Label for="localisation">Localisation</Label>
                                    <Input id="localisation" v-model="form.localisation" type="text"
                                        :class="{ 'border-red-300': form.errors.localisation }" />
                                    <p v-if="form.errors.localisation" class="text-sm text-red-600">{{
                                        form.errors.localisation }}</p>
                                </div>

                                <!-- Description -->
                                <div class="space-y-2">
                                    <Label for="description">Description</Label>
                                    <textarea id="description" v-model="form.description"
                                        class="w-full border rounded-md p-2"
                                        :class="{ 'border-red-300': form.errors.description }" rows="4"></textarea>
                                    <p v-if="form.errors.description" class="text-sm text-red-600">{{
                                        form.errors.description }}</p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <Link :href="route('projets.batiments.index', props.projet.id)"
                                        class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                                        Annuler
                                    </Link>

                                    <div class="flex items-center gap-3">
                                        <Button type="submit" :disabled="form.processing"
                                            class="bg-blue-600 hover:bg-blue-700">
                                            <span v-if="form.processing" class="flex items-center">
                                                <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                                                Création...
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
