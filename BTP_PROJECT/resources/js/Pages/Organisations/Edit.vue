<template>
    <Head title="Modifier l'organisation" />

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
                                        Modifier l'organisation
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        Mettez à jour les informations de l'organisation
                                    </CardDescription>
                                </div>

                                <Link :href="route('organisations.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="submit" class="space-y-6" enctype="multipart/form-data">
                                <!-- Nom -->
                                <div class="space-y-2">
                                    <Label for="nom">
                                        Nom <span class="text-red-500">*</span>
                                    </Label>
                                    <div class="relative">
                                        <div class="absolute left-3 top-1/2 -translate-y-1/2">
                                            <Building2 class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <Input id="nom" v-model="form.nom" type="text"
                                            class="pl-10 w-full"
                                            :class="{ 'border-red-300': form.errors.nom }" required />
                                    </div>
                                    <p v-if="form.errors.nom" class="text-sm text-red-600">
                                        {{ form.errors.nom }}
                                    </p>
                                </div>

                                <!-- Raison sociale -->
                                <div class="space-y-2">
                                    <Label for="raison_sociale">
                                        Raison sociale <span class="text-red-500">*</span>
                                    </Label>
                                    <Input id="raison_sociale" v-model="form.raison_sociale" type="text"
                                        :class="{ 'border-red-300': form.errors.raison_sociale }" required />
                                    <p v-if="form.errors.raison_sociale" class="text-sm text-red-600">
                                        {{ form.errors.raison_sociale }}
                                    </p>
                                </div>

                                <!-- Logo existant -->
                                <div v-if="organisation.logo" class="space-y-2">
                                    <Label>
                                        Logo actuel
                                    </Label>
                                    <div class="flex items-center gap-4">
                                        <img :src="`/storage/${organisation.logo}`" alt="Logo organisation"
                                            class="h-16 w-16 rounded-lg object-contain border" />
                                        <p class="text-sm text-gray-500">
                                            Importez un nouveau logo pour le remplacer
                                        </p>
                                    </div>
                                </div>

                                <!-- Nouveau logo -->
                                <div class="space-y-2">
                                    <Label for="logo">
                                        Nouveau logo
                                    </Label>
                                    <Input id="logo" type="file" accept="image/*" @change="handleLogo"
                                        :class="{ 'border-red-300': form.errors.logo }" />
                                    <p v-if="form.errors.logo" class="text-sm text-red-600">
                                        {{ form.errors.logo }}
                                    </p>
                                </div>

                                <!-- Adresse -->
                                <div class="space-y-2">
                                    <Label for="adresse">
                                        Adresse
                                    </Label>
                                    <Input id="adresse" v-model="form.adresse" type="text"
                                        :class="{ 'border-red-300': form.errors.adresse }" />
                                    <p v-if="form.errors.adresse" class="text-sm text-red-600">
                                        {{ form.errors.adresse }}
                                    </p>
                                </div>

                                <!-- Pays & Devise -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="pays">
                                            Pays
                                        </Label>
                                        <Input id="pays" v-model="form.pays" type="text"
                                            :class="{ 'border-red-300': form.errors.pays }" />
                                        <p v-if="form.errors.pays" class="text-sm text-red-600">
                                            {{ form.errors.pays }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="devise">
                                            Devise
                                        </Label>
                                        <Input id="devise" v-model="form.devise" type="text" maxlength="3"
                                            :class="{ 'border-red-300': form.errors.devise }" />
                                        <p v-if="form.errors.devise" class="text-sm text-red-600">
                                            {{ form.errors.devise }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <Link :href="route('organisations.index')"
                                        class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                                        Annuler
                                    </Link>

                                    <div class="flex items-center gap-3">
                                        <Transition enter-active-class="transition-opacity duration-300"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition-opacity duration-300"
                                            leave-to-class="opacity-0">
                                            <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                                                Organisation mise à jour avec succès !
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
import { ArrowLeft, Loader2, Save, Building2 } from 'lucide-vue-next'

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

const props = defineProps({
    organisation: {
        type: Object,
        required: true
    }
})

const currentPageTitle = ref('Modifier Organisation')

const form = useForm({
    nom: props.organisation.nom,
    raison_sociale: props.organisation.raison_sociale,
    logo: null,
    adresse: props.organisation.adresse,
    pays: props.organisation.pays,
    devise: props.organisation.devise,
})

const handleLogo = (event) => {
    form.logo = event.target.files[0]
}

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            _method: 'put',
        }))
        .post(route('organisations.update', props.organisation.id), {
            forceFormData: true,
            preserveScroll: true,
        })
}
</script>
