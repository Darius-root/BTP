<template>

    <Head :title="currentPageTitle" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">

                        <CardHeader class="px-0 pt-0">
                            <CardTitle class="text-3xl">
                                Modifier un niveau
                            </CardTitle>
                            <CardDescription class="mt-1">
                                Modification du niveau
                                <strong>{{ props.niveau.nom }}</strong>
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="submit" class="space-y-6">

                            

                                <!-- Code -->
                                <div class="space-y-2">
                                    <Label for="code">
                                        Code <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="code"
                                        v-model="form.code"
                                        type="text"
                                        placeholder="Ex : NIV-RDC"
                                        :class="{ 'border-red-300': form.errors.code }"
                                        required
                                    />
                                    <p v-if="form.errors.code" class="text-sm text-red-600">
                                        {{ form.errors.code }}
                                    </p>
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
                                    <p v-if="form.errors.nom" class="text-sm text-red-600">
                                        {{ form.errors.nom }}
                                    </p>
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
                                        :href="route('batiments.niveaux.index', props.niveau.batiment_id)"
                                        class="text-sm text-gray-600 hover:text-gray-900"
                                    >
                                        Annuler
                                    </Link>

                                    <Button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="bg-blue-600 hover:bg-blue-700"
                                    >
                                        <span v-if="form.processing">Enregistrement...</span>
                                        <span v-else>Enregistrer</span>
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

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

const props = defineProps({
    niveau: {
        type: Object,
        required: true,
    },
})

const currentPageTitle = computed(() =>
    `Modifier le niveau ${props.niveau.nom}`
)

const form = useForm({
    // batiment_id NON MODIFIABLE mais conservé
    batiment_id: props.niveau.batiment_id,
    code: props.niveau.code,
    nom: props.niveau.nom,
    description: props.niveau.description ?? '',
})

const submit = () => {
    form.put(route('niveaux-batiment.update', props.niveau.id), {
        preserveScroll: true,
    })
}
</script>

