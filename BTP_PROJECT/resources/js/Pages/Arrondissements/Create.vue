<template>
    <Head title="Nouvel Arrondissement" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <!-- Card du formulaire -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Créer un arrondissement</CardTitle>
                                    <CardDescription class="mt-1">
                                        Remplissez les informations du nouvel arrondissement
                                    </CardDescription>
                                </div>
                                <Link
                                    :href="route('arrondissements.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors"
                                >
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="form.post(route('arrondissements.store'))" class="space-y-6">

                                <!-- Code -->
                                <div class="space-y-2">
                                    <Label for="code" class="text-sm font-medium">
                                        Code <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="code"
                                        type="text"
                                        v-model="form.code"
                                        placeholder="Ex: ARR001"
                                        required
                                        class="w-full"
                                        :class="{ 'border-red-300': form.errors.code }"
                                    />
                                    <p v-if="form.errors.code" class="text-sm text-red-600">
                                        {{ form.errors.code }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Code unique identifiant l'arrondissement
                                    </p>
                                </div>

                                <!-- Libellé -->
                                <div class="space-y-2">
                                    <Label for="libelle" class="text-sm font-medium">
                                        Libellé <span class="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="libelle"
                                        type="text"
                                        v-model="form.libelle"
                                        placeholder="Ex: Arrondissement Centre"
                                        required
                                        class="w-full"
                                        :class="{ 'border-red-300': form.errors.libelle }"
                                    />
                                    <p v-if="form.errors.libelle" class="text-sm text-red-600">
                                        {{ form.errors.libelle }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Nom complet de l'arrondissement
                                    </p>
                                </div>

                                <!-- Commune -->
                                <div class="space-y-2">
                                    <Label for="commune_id" class="text-sm font-medium">
                                        Commune <span class="text-red-500">*</span>
                                    </Label>
                                    <select
                                        id="commune_id"
                                        v-model="form.commune_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                        :class="{ 'border-red-300': form.errors.commune_id }"
                                    >
                                        <option value="">Sélectionnez une commune</option>
                                        <option
                                            v-for="commune in communes"
                                            :key="commune.id"
                                            :value="commune.id"
                                            class="dark:bg-gray-800 dark:text-gray-100"
                                        >
                                            {{ commune.libelle }} ({{ commune.code }})
                                        </option>
                                    </select>
                                    <p v-if="form.errors.commune_id" class="text-sm text-red-600">
                                        {{ form.errors.commune_id }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Commune à laquelle appartient l'arrondissement
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
                                    <Link
                                        :href="route('arrondissements.index')"
                                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 transition-colors"
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
                                                class="text-sm text-green-600 dark:text-green-400"
                                            >
                                                Arrondissement créé avec succès !
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
                                                Créer l'arrondissement
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
import { ArrowLeft, Loader2, Plus } from 'lucide-vue-next'
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

const currentPageTitle = ref("Nouvel Arrondissement")

const props = defineProps({
    communes: {
        type: Array,
        required: true,
    }
})

const form = useForm({
    code: '',
    libelle: '',
    commune_id: ''
})
</script>
