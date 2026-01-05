<template>
    <Head :title="`Modifier Devise - ${devise.code}`" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/3 lg:p-6">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <CardTitle class="text-3xl">
                                        Modifier la devise
                                    </CardTitle>

                                    <CardDescription class="mt-1">
                                        {{ devise.libelle }}
                                        <span class="text-gray-500">
                                            ({{ devise.code }})
                                        </span>

                                        <span v-if="devise.symbole" class="block text-sm mt-1">
                                            Symbole : {{ devise.symbole }}
                                        </span>
                                    </CardDescription>
                                </div>

                                <Link
                                    :href="route('devises.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900"
                                >
                                    <ArrowLeft class="mr-2 h-4 w-4" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form
                                @submit.prevent="form.put(route('devises.update', devise.id))"
                                class="space-y-6"
                            >
                                <!-- Code -->
                                <div class="space-y-2">
                                    <Label for="code">Code *</Label>
                                    <Input
                                        id="code"
                                        v-model="form.code"
                                        placeholder="Ex : XOF, EUR, USD"
                                    />
                                    <p v-if="form.errors.code" class="text-sm text-red-600">
                                        {{ form.errors.code }}
                                    </p>
                                </div>

                                <!-- Libellé -->
                                <div class="space-y-2">
                                    <Label for="libelle">Libellé *</Label>
                                    <Input
                                        id="libelle"
                                        v-model="form.libelle"
                                        placeholder="Ex : Franc CFA"
                                    />
                                    <p v-if="form.errors.libelle" class="text-sm text-red-600">
                                        {{ form.errors.libelle }}
                                    </p>
                                </div>

                                <!-- Symbole -->
                                <div class="space-y-2">
                                    <Label for="symbole">Symbole</Label>
                                    <Input
                                        id="symbole"
                                        v-model="form.symbole"
                                        placeholder="Ex : ₣, €, $"
                                    />
                                    <p v-if="form.errors.symbole" class="text-sm text-red-600">
                                        {{ form.errors.symbole }}
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

const currentPageTitle = ref('Modifier Devise')

const { devise } = defineProps({
    devise: Object,
})

const form = useForm({
    code: devise.code,
    libelle: devise.libelle,
    symbole: devise.symbole,
})

const confirmDelete = () => {
    if (
        confirm(
            `Supprimer définitivement la devise "${devise.libelle}" (${devise.code}) ?`
        )
    ) {
        router.delete(route('devises.destroy', devise.id))
    }
}
</script>
