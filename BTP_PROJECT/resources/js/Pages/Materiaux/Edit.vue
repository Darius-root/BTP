<template>

    <Head :title="`Modifier Matériau - ${materiau.nom}`" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div
                    class="rounded-2xl border  p-5 shadow-sm  lg:p-6">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <CardTitle class="text-3xl">
                                        Modifier le matériel
                                    </CardTitle>
                                    <CardDescription class="mt-1">
                                        {{ materiau.nom }}
                                        <span class="text-gray-500">
                                            ({{ materiau.code }})
                                        </span>
                                        <span v-if="materiau.unite" class="block text-sm mt-1">
                                            Unité : {{ materiau.unite.libelle }}
                                        </span>
                                    </CardDescription>
                                </div>

                                <Link :href="route('materiaux.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900">
                                    <ArrowLeft class="mr-2 h-4 w-4" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="form.put(route('materiaux.update', materiau.id))" class="space-y-6">
                                

                                <!-- Nom -->
                                <div class="space-y-2">
                                    <Label for="nom">Nom *</Label>
                                    <Input id="nom" v-model="form.nom" placeholder="Ex : Ciment Portland" />
                                    <p v-if="form.errors.nom" class="text-sm text-red-600">
                                        {{ form.errors.nom }}
                                    </p>
                                </div>

                                <!-- Unité -->
                                <div class="space-y-2">
                                    <Label for="unite_id">Unité de mesure *</Label>
                                    <select id="unite_id" v-model="form.unite_id"
                                        class="w-full rounded-md border px-3 py-2">
                                        <option value="">Sélectionnez une unité</option>
                                        <option v-for="unite in unites" :key="unite.id" :value="unite.id">
                                            {{ unite.libelle }} ({{ unite.code }})
                                        </option>
                                    </select>
                                    <p v-if="form.errors.unite_id" class="text-sm text-red-600">
                                        {{ form.errors.unite_id }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-end border-t pt-6">
                                   

                                    <Button type="submit" :disabled="form.processing"
                                        class="bg-blue-600 hover:bg-blue-700">
                                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                        <Save v-else class="mr-2 h-4 w-4" />
                                        Mettre à jour
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
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { ArrowLeft, Loader2, Save, Trash2 } from 'lucide-vue-next'

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

const currentPageTitle = ref('Modifier Matériau')



const { materiau, unites } = defineProps({
    materiau: Object,
    unites: Array,
})


const form = useForm({
    nom: materiau.nom,
    unite_id: materiau.unite_id,
})

const confirmDelete = () => {
    if (
        confirm(
            `Supprimer définitivement "${materiau.nom}" (${materiau.code}) ?`
        )
    ) {
        router.delete(route('materiaux.destroy', materiau.id))
    }
}
</script>
