<template>

    <Head :title="currentPageTitle" />

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
                                        Créer un niveau
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        {{ props.batiment?.nom
                                            ? `Ajoutez un niveau au bâtiment "${props.batiment.nom}"`
                                            : 'Ajoutez un nouveau niveau de bâtiment'
                                        }}
                                    </CardDescription>
                                </div>

                                <Link
                                    :href="props.batiment
                                        ? route('batiments.niveaux.index', props.batiment.id)
                                        : route('niveaux-batiment.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors"
                                >
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="submit" class="space-y-6">

                                <!-- Bâtiment -->
                                <div v-if="!props.batiment" class="space-y-2">
                                    <Label for="batiment_id">
                                        Bâtiment <span class="text-red-500">*</span>
                                    </Label>
                                    <select
                                        id="batiment_id"
                                        v-model="form.batiment_id"
                                        class="flex h-10 w-full rounded-md border bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                                        :class="{ 'border-red-300': form.errors.batiment_id }"
                                    >
                                        <option value="">Sélectionner un bâtiment</option>
                                        <option
                                            v-for="bat in safeBatiments"
                                            :key="bat.id"
                                            :value="bat.id"
                                        >
                                            {{ bat.nom }} — {{ bat.projet?.nom }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.batiment_id" class="text-sm text-red-600">
                                        {{ form.errors.batiment_id }}
                                    </p>
                                </div>

                                <!-- Code -->
                                <div class="space-y-2">
                                    <Label for="code">
                                        Code <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="code"
                                        v-model="form.code"
                                        type="text"
                                        placeholder="Ex : NIV-RDC, NIV-01"
                                        :class="{ 'border-red-300': form.errors.code }"
                                        required
                                    />
                                </div>

                                <!-- Nom -->
                                <div class="space-y-2">
                                    <Label for="nom">
                                        Nom du niveau <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="nom"
                                        v-model="form.nom"
                                        type="text"
                                        placeholder="Ex : Rez-de-chaussée"
                                        :class="{ 'border-red-300': form.errors.nom }"
                                        required
                                    />
                                </div>

                                <!-- Description -->
                                <div class="space-y-2">
                                    <Label for="description">Description</Label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="4"
                                        class="w-full border rounded-md p-2"
                                        :class="{ 'border-red-300': form.errors.description }"
                                    />
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <Link
                                        :href="props.batiment
                                            ? route('batiments.niveaux.index', props.batiment.id)
                                            : route('niveaux-batiment.index')"
                                        class="text-sm text-gray-600 hover:text-gray-900 transition-colors"
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
                                            <Save class="w-4 h-4 mr-2" />
                                            Enregistrer
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
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Save, ArrowLeft, Loader2 } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

const props = defineProps({
    batiment: {
        type: Object,
        default: null,
    },
    batiments: {
        type: Array,
        default: () => [],
    },
})

/**
 * Normalisation défensive
 */
const safeBatiments = computed(() => Array.isArray(props.batiments) ? props.batiments : [])

/**
 * Titre dérivé (pas un ref)
 */
const currentPageTitle = computed(() =>
    props.batiment?.nom
        ? `Créer un niveau pour ${props.batiment.nom}`
        : 'Créer un niveau'
)

const form = useForm({
    batiment_id: props.batiment?.id ?? '',
    code: '',
    nom: '',
    description: '',
})

const submit = () => {
    form.post(route('niveaux-batiment.store'), {
        preserveScroll: true,
        onError: (errors) => console.error('Erreurs validation:', errors),
        onSuccess: () => console.info('Niveau créé avec succès'),
    })
}
</script>



