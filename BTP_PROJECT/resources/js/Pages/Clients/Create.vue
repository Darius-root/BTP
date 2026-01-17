<template>
    <Head title="Nouveau client" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div class="rounded-2xl border  p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Créer un client</CardTitle>
                                    <CardDescription class="mt-1">
                                        Remplissez les informations du nouveau client
                                    </CardDescription>
                                </div>

                                <Link :href="route('clients.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                                    <ArrowLeft class="w-4 h-4 mr-2" /> Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="submit" class="space-y-6">

                                <!-- Nom -->
                                <div class="space-y-2">
                                    <Label for="nom">
                                        Nom <span class="text-red-500">*</span>
                                    </Label>
                                    <Input id="nom" v-model="form.nom" type="text"
                                        :class="{ 'border-red-300': form.errors.nom }" required />
                                    <p v-if="form.errors.nom" class="text-sm text-red-600">
                                        {{ form.errors.nom }}
                                    </p>
                                </div>

                                <!-- Société -->
                                <div class="space-y-2">
                                    <Label for="societe">Société</Label>
                                    <Input id="societe" v-model="form.societe" type="text"
                                        :class="{ 'border-red-300': form.errors.societe }" />
                                    <p v-if="form.errors.societe" class="text-sm text-red-600">
                                        {{ form.errors.societe }}
                                    </p>
                                </div>

                                <!-- Email -->
                                <div class="space-y-2">
                                    <Label for="email">
                                        Email <span class="text-red-500">*</span>
                                    </Label>
                                    <Input id="email" v-model="form.email" type="email"
                                        :class="{ 'border-red-300': form.errors.email }" required />
                                    <p v-if="form.errors.email" class="text-sm text-red-600">
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <!-- Téléphone -->
                                <div class="space-y-2">
                                    <Label for="telephone">Téléphone</Label>
                                    <Input id="telephone" v-model="form.telephone" type="text"
                                        :class="{ 'border-red-300': form.errors.telephone }" />
                                    <p v-if="form.errors.telephone" class="text-sm text-red-600">
                                        {{ form.errors.telephone }}
                                    </p>
                                </div>

                                <!-- Adresse -->
                                <div class="space-y-2">
                                    <Label for="adresse">Adresse</Label>
                                    <Input id="adresse" v-model="form.adresse" type="text"
                                        :class="{ 'border-red-300': form.errors.adresse }" />
                                    <p v-if="form.errors.adresse" class="text-sm text-red-600">
                                        {{ form.errors.adresse }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <Link :href="route('clients.index')"
                                        class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                                        Annuler
                                    </Link>

                                    <div class="flex items-center gap-3">
                                        <Transition enter-active-class="transition-opacity duration-300"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition-opacity duration-300"
                                            leave-to-class="opacity-0">
                                            <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                                                Client créé avec succès !
                                            </p>
                                        </Transition>

                                        <Button type="submit" :disabled="form.processing"
                                            class="bg-blue-600 hover:bg-blue-700">
                                            <span v-if="form.processing" class="flex items-center">
                                                <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                                                Création...
                                            </span>
                                            <span v-else class="flex items-center">
                                                <Plus class="w-4 h-4 mr-2" />
                                                Créer le client
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
import { Plus, Loader2, ArrowLeft } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

const currentPageTitle = ref('Nouveau Client')

// Organisation active en dur
const ACTIVE_ORGANISATION_ID = 1

const form = useForm({
    nom: '',
    societe: '',
    email: '',
    telephone: '',
    adresse: '',
    organisation_id: ACTIVE_ORGANISATION_ID,
})

const submit = () => {
    form.post(route('clients.store'))
}
</script>
