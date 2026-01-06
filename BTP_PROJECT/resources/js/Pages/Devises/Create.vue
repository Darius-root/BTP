<template>
    <Head title="Nouvelle Devise" />

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
                                    <CardTitle class="text-3xl">Créer une devise</CardTitle>
                                    <CardDescription class="mt-1">
                                        Ajoutez une nouvelle devise monétaire au système
                                    </CardDescription>
                                </div>
                                <Link
                                    :href="route('devises.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors"
                                >
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="form.post(route('devises.store'))" class="space-y-6">

                                <!-- Code -->
                                <div class="space-y-2">
                                    <Label for="code" class="text-sm font-medium">
                                        Code ISO <span class="text-red-500">*</span>
                                    </Label>
                                    <div class="relative">
                                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                            <Globe class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <Input
                                            id="code"
                                            type="text"
                                            v-model="form.code"
                                            placeholder="Ex: XOF, EUR, USD"
                                            required
                                            maxlength="3"
                                            class="pl-10 w-full font-mono text-center uppercase"
                                            :class="{ 'border-red-300': form.errors.code }"
                                        />
                                    </div>
                                    <p v-if="form.errors.code" class="text-sm text-red-600">
                                        {{ form.errors.code }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Code ISO 4217 (3 lettres majuscules)
                                    </p>
                                </div>

                                <!-- Libellé -->
                                <div class="space-y-2">
                                    <Label for="libelle" class="text-sm font-medium">
                                        Libellé <span class="text-red-500">*</span>
                                    </Label>
                                    <div class="relative">
                                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                            <FileText class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <Input
                                            id="libelle"
                                            type="text"
                                            v-model="form.libelle"
                                            placeholder="Ex: Franc CFA, Euro, Dollar US"
                                            required
                                            class="pl-10 w-full"
                                            :class="{ 'border-red-300': form.errors.libelle }"
                                        />
                                    </div>
                                    <p v-if="form.errors.libelle" class="text-sm text-red-600">
                                        {{ form.errors.libelle }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Nom complet de la devise
                                    </p>
                                </div>

                                <!-- Symbole -->
                                <div class="space-y-2">
                                    <Label for="symbole" class="text-sm font-medium">
                                        Symbole monétaire
                                    </Label>
                                    <div class="relative">
                                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                            <DollarSign class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <Input
                                            id="symbole"
                                            type="text"
                                            v-model="form.symbole"
                                            placeholder="Ex: F CFA, €, $"
                                            maxlength="10"
                                            class="pl-10 w-full"
                                            :class="{ 'border-red-300': form.errors.symbole }"
                                        />
                                    </div>
                                    <p v-if="form.errors.symbole" class="text-sm text-red-600">
                                        {{ form.errors.symbole }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Symbole monétaire (optionnel)
                                    </p>

                                    <!-- Aperçu du symbole -->
                                    <div v-if="form.symbole" class="mt-2 p-3 bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/10 dark:to-yellow-800/10 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Aperçu :</div>
                                        <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">
                                            1 000 <span class="ml-1">{{ form.symbole }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
                                    <Link
                                        :href="route('devises.index')"
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
                                                Devise créée avec succès !
                                            </p>
                                        </Transition>
                                        <Button
                                            type="submit"
                                            :disabled="form.processing"
                                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800"
                                        >
                                            <span v-if="form.processing" class="flex items-center">
                                                <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                                                Création...
                                            </span>
                                            <span v-else class="flex items-center">
                                                <Banknote class="w-4 h-4 mr-2" />
                                                Créer la devise
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
import { ArrowLeft, Loader2, Plus, Banknote, Globe, FileText, DollarSign } from 'lucide-vue-next'
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

const currentPageTitle = ref("Nouvelle Devise")

const form = useForm({
    code: '',
    libelle: '',
    symbole: ''
})
</script>
