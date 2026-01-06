<template>
    <Head :title="`Modifier Corps d'État - ${corpsEtat.intitule}`" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/3 lg:p-6"
                >
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <CardTitle class="text-3xl">
                                        Modifier le corps d’état
                                    </CardTitle>

                                    <CardDescription class="mt-1">
                                        {{ corpsEtat.intitule }}
                                        <span class="text-gray-500">
                                            ({{ corpsEtat.code }})
                                        </span>

                                        <span class="block text-sm mt-1 text-gray-500">
                                            Ordre : {{ corpsEtat.ordre }}
                                        </span>
                                    </CardDescription>
                                </div>

                                <Link
                                    :href="route('corps-etat.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900"
                                >
                                    <ArrowLeft class="mr-2 h-4 w-4" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form
                                @submit.prevent="form.put(route('corps-etat.update', corpsEtat.id))"
                                class="space-y-6"
                            >
                                <!-- Code -->
                                <div class="space-y-2">
                                    <Label for="code">Code *</Label>
                                    <Input
                                        id="code"
                                        v-model="form.code"
                                        placeholder="Ex : GO, VRD, ELEC"
                                    />
                                    <p v-if="form.errors.code" class="text-sm text-red-600">
                                        {{ form.errors.code }}
                                    </p>
                                </div>

                                <!-- Intitulé -->
                                <div class="space-y-2">
                                    <Label for="intitule">Intitulé *</Label>
                                    <Input
                                        id="intitule"
                                        v-model="form.intitule"
                                        placeholder="Ex : Gros œuvre"
                                    />
                                    <p v-if="form.errors.intitule" class="text-sm text-red-600">
                                        {{ form.errors.intitule }}
                                    </p>
                                </div>

                                <!-- Ordre -->
                                <div class="space-y-2">
                                    <Label for="ordre">Ordre *</Label>
                                    <Input
                                        id="ordre"
                                        type="number"
                                        v-model="form.ordre"
                                        placeholder="Ex : 1"
                                    />
                                    <p v-if="form.errors.ordre" class="text-sm text-red-600">
                                        {{ form.errors.ordre }}
                                    </p>
                                </div>

                                <!-- Sous-total -->
                                <div class="space-y-2">
                                    <Label for="sous_total">Sous-total</Label>
                                    <Input
                                        id="sous_total"
                                        type="number"
                                        step="0.01"
                                        v-model="form.sous_total"
                                        placeholder="Ex : 1250000"
                                    />
                                    <p v-if="form.errors.sous_total" class="text-sm text-red-600">
                                        {{ form.errors.sous_total }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between border-t pt-6">
                                    <button
                                        type="button"
                                        @click="confirmDelete"
                                        class="text-sm text-red-600 hover:text-red-800"
                                    >
                                        <Trash2 class="inline mr-1 h-4 w-4" />
                                        Supprimer
                                    </button>

                                    <Button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="bg-blue-600 hover:bg-blue-700"
                                    >
                                        <Loader2
                                            v-if="form.processing"
                                            class="mr-2 h-4 w-4 animate-spin"
                                        />
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
    CardDescription,
} from '@/components/ui/card'

import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

const currentPageTitle = ref("Modifier Corps d'État")

const { corpsEtat } = defineProps({
    corpsEtat: Object,
})

const form = useForm({
    code: corpsEtat.code,
    intitule: corpsEtat.intitule,
    ordre: corpsEtat.ordre,
    sous_total: corpsEtat.sous_total,
})

const confirmDelete = () => {
    if (
        confirm(
            `Supprimer définitivement le corps d'état "${corpsEtat.intitule}" (${corpsEtat.code}) ?`
        )
    ) {
        router.delete(route('corps-etat.destroy', corpsEtat.id))
    }
}
</script>
