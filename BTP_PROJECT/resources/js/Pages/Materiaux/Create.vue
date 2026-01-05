<template>

    <Head title="Nouveau Matériau" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <!-- Card du formulaire -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Créer un matériau</CardTitle>
                                    <CardDescription class="mt-1">
                                        Remplissez les informations du nouveau matériau
                                    </CardDescription>
                                </div>
                                <Link :href="route('materiaux.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="form.post(route('materiaux.store'))" class="space-y-6">

                                <!-- Code -->
                                <div class="space-y-2">
                                    <Label for="code" class="text-sm font-medium">
                                        Code <span class="text-red-500">*</span>
                                    </Label>
                                    <div class="relative">
                                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                            <Package class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <Input id="code" type="text" v-model="form.code" placeholder="Ex: MAT001"
                                            required class="pl-10 w-full"
                                            :class="{ 'border-red-300': form.errors.code }" />
                                    </div>
                                    <p v-if="form.errors.code" class="text-sm text-red-600">
                                        {{ form.errors.code }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Code unique identifiant le matériau
                                    </p>
                                </div>

                                <!-- Nom -->
                                <div class="space-y-2">
                                    <Label for="nom" class="text-sm font-medium">
                                        Nom <span class="text-red-500">*</span>
                                    </Label>
                                    <Input id="nom" type="text" v-model="form.nom"
                                        placeholder="Ex: Ciment, Sable, Fer à béton" required class="w-full"
                                        :class="{ 'border-red-300': form.errors.nom }" />
                                    <p v-if="form.errors.nom" class="text-sm text-red-600">
                                        {{ form.errors.nom }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Nom complet du matériau
                                    </p>
                                </div>

                                <!-- Unité de mesure -->
                                <div class="space-y-2">
                                    <Label for="unite_id" class="text-sm font-medium">
                                        Unité de mesure <span class="text-red-500">*</span>
                                    </Label>
                                    <div class="relative">
                                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                            <Ruler class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <select id="unite_id" v-model="form.unite_id" required
                                            class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                            :class="{ 'border-red-300': form.errors.unite_id }">
                                            <option value="">Sélectionnez une unité</option>
                                            <option v-for="unite in unites" :key="unite.id" :value="unite.id"
                                                class="dark:bg-gray-800 dark:text-gray-100">
                                                {{ unite.libelle }} ({{ unite.code }})
                                            </option>
                                        </select>
                                    </div>
                                    <p v-if="form.errors.unite_id" class="text-sm text-red-600">
                                        {{ form.errors.unite_id }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Unité de mesure utilisée pour ce matériau
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div
                                    class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
                                    <Link :href="route('materiaux.index')"
                                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 transition-colors">
                                        Annuler
                                    </Link>
                                    <div class="flex items-center gap-3">
                                        <Transition enter-active-class="transition-opacity duration-300"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition-opacity duration-300"
                                            leave-to-class="opacity-0">
                                            <p v-if="form.recentlySuccessful"
                                                class="text-sm text-green-600 dark:text-green-400">
                                                Matériau créé avec succès !
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
                                                Créer le matériau
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
import { ArrowLeft, Loader2, Plus, Package, Ruler } from 'lucide-vue-next'
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

const currentPageTitle = ref("Nouveau Matériau")

const props = defineProps({
    unites: {
        type: Array,
        required: true,
    }
})

const form = useForm({
    code: '',
    nom: '',
    unite_id: ''
})
</script>
