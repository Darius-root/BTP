<template>

    <Head :title="`Modifier Devise - ${devise.code}`" />

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

                                <Link :href="route('devises.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900">
                                    <ArrowLeft class="mr-2 h-4 w-4" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="form.put(route('devises.update', devise.id))" class="space-y-6">
                                <!-- Libellé -->
                                <div class="space-y-2">
                                    <Label for="libelle">Libellé *</Label>
                                    <Input id="libelle" v-model="form.libelle" placeholder="Ex : Franc CFA" />
                                    <p v-if="form.errors.libelle" class="text-sm text-red-600">
                                        {{ form.errors.libelle }}
                                    </p>
                                </div>

                                <!-- Symbole -->
                                <div class="space-y-2">
                                    <Label for="symbole">Symbole</Label>
                                    <Input id="symbole" v-model="form.symbole" placeholder="Ex : ₣, €, $" />
                                    <p v-if="form.errors.symbole" class="text-sm text-red-600">
                                        {{ form.errors.symbole }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between border-t pt-6">
                                    <!-- ✅ Bouton suppression ouvre le DeleteDialog -->
                                    <button type="button" @click="deleteOpen = true"
                                        class="text-sm text-red-600 hover:text-red-800">
                                        <Trash2 class="inline mr-1 h-4 w-4" />
                                        Supprimer
                                    </button>

                                    <Button type="submit" :disabled="form.processing"
                                        class="bg-blue-600 hover:bg-blue-700 text-white">
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

            <!-- ✅ Dialog suppression -->
            <DeleteDialog :open="deleteOpen" :item="devise" resource="devises"
                :label="`la devise ${devise.libelle} (${devise.code})`" @update:open="deleteOpen = $event" />
        </AdminLayout>
    </SidebarProvider>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
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
import DeleteDialog from '@/components/DeleteDialog.vue'

const currentPageTitle = ref('Modifier Devise')

const { devise } = defineProps < {
    devise: {
        id: number
    code: string
    libelle: string
    symbole?: string
    }
} > ()

const form = useForm({
    libelle: devise.libelle,
    symbole: devise.symbole,
})

const deleteOpen = ref(false)
</script>
