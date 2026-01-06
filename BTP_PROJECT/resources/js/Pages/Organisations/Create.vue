<template>
    <Head title="Nouvelle Organisation" />

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
                                        Créer une organisation
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        Renseignez les informations de la nouvelle organisation
                                    </CardDescription>
                                </div>

                                <Link
                                    :href="route('organisations.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors"
                                >
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form
                                @submit.prevent="submit"
                                class="space-y-6"
                                enctype="multipart/form-data"
                            >
                                <!-- Nom -->
                                <div class="space-y-2">
                                    <Label for="name">
                                        Nom <span class="text-red-500">*</span>
                                    </Label>
                                    <div class="relative">
                                        <div class="absolute left-3 top-1/2 -translate-y-1/2">
                                            <Building2 class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            placeholder="Ex : Mon Organisation"
                                            class="pl-10 w-full"
                                            :class="{ 'border-red-300': form.errors.name }"
                                            required
                                        />
                                    </div>
                                    <p v-if="form.errors.name" class="text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <!-- Raison sociale -->
                                <div class="space-y-2">
                                    <Label for="raison_sociale">
                                        Raison sociale <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="raison_sociale"
                                        v-model="form.raison_sociale"
                                        type="text"
                                        placeholder="Ex : Société ABC SARL"
                                        :class="{ 'border-red-300': form.errors.raison_sociale }"
                                        required
                                    />
                                    <p v-if="form.errors.raison_sociale" class="text-sm text-red-600">
                                        {{ form.errors.raison_sociale }}
                                    </p>
                                </div>

                                <!-- Logo -->
                                <div class="space-y-2">
                                    <Label for="logo">
                                        Logo
                                    </Label>
                                    <Input
                                        id="logo"
                                        type="file"
                                        accept="image/*"
                                        @change="handleLogo"
                                        :class="{ 'border-red-300': form.errors.logo }"
                                    />
                                    <p v-if="form.errors.logo" class="text-sm text-red-600">
                                        {{ form.errors.logo }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Format image – taille max 2 Mo
                                    </p>
                                </div>

                                <!-- Adresse -->
                                <div class="space-y-2">
                                    <Label for="adresse">
                                        Adresse
                                    </Label>
                                    <Input
                                        id="adresse"
                                        v-model="form.adresse"
                                        type="text"
                                        placeholder="Adresse complète"
                                        :class="{ 'border-red-300': form.errors.adresse }"
                                    />
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
                                        <Input
                                            id="pays"
                                            v-model="form.pays"
                                            type="text"
                                            placeholder="Ex : Bénin"
                                            :class="{ 'border-red-300': form.errors.pays }"
                                        />
                                        <p v-if="form.errors.pays" class="text-sm text-red-600">
                                            {{ form.errors.pays }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="devise">
                                            Slogan
                                        </Label>
                                        <Input
                                            id="devise"
                                            v-model="form.devise"
                                            type="text"
                                            maxlength="3"
                                            placeholder="Ex : FORCE - COURAGE - DISCIPLINE"
                                            :class="{ 'border-red-300': form.errors.devise }"
                                        />
                                        <p v-if="form.errors.devise" class="text-sm text-red-600">
                                            {{ form.errors.devise }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <Link
                                        :href="route('organisations.index')"
                                        class="text-sm text-gray-600 hover:text-gray-900 transition-colors"
                                    >
                                        Annuler
                                    </Link>

                                    <div class="flex items-center gap-3">
                                        <Transition
                                            enter-active-class="transition-opacity duration-300"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition-opacity duration-300"
                                            leave-to-class="opacity-0"
                                        >
                                            <p
                                                v-if="form.recentlySuccessful"
                                                class="text-sm text-green-600"
                                            >
                                                Organisation créée avec succès !
                                            </p>
                                        </Transition>

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
                                                Créer l'organisation
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
import { ArrowLeft, Loader2, Plus, Building2 } from 'lucide-vue-next'

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

const currentPageTitle = ref('Nouvelle Organisation')

const form = useForm({
    name: '',
    raison_sociale: '',
    logo: null,
    adresse: '',
    pays: '',
    devise: '',
})

const handleLogo = (event) => {
    form.logo = event.target.files[0]
}

const submit = () => {
    form.post(route('organisations.store'), {
        forceFormData: true,
        preserveScroll: true,
    })
}
</script>
