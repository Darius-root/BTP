<template>
    <Head title="Nouveau Corps d'État" />

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
                                    <CardTitle class="text-3xl">Créer un corps d'état</CardTitle>
                                    <CardDescription class="mt-1">
                                        Définissez une nouvelle catégorie de travaux
                                    </CardDescription>
                                </div>
                                <Link
                                    :href="route('corps-etat.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors"
                                >
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="form.post(route('corps-etat.store'))" class="space-y-6">

                                <!-- Ordre -->
                                <div class="space-y-2">
                                    <Label for="ordre" class="text-sm font-medium">
                                        Ordre de tri <span class="text-red-500">*</span>
                                    </Label>
                                    <div class="relative">
                                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                            <ListOrdered class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <Input
                                            id="ordre"
                                            type="number"
                                            v-model="form.ordre"
                                            placeholder="Ex: 1, 2, 3..."
                                            required
                                            min="1"
                                            class="pl-10 w-full"
                                            :class="{ 'border-red-300': form.errors.ordre }"
                                        />
                                    </div>
                                    <p v-if="form.errors.ordre" class="text-sm text-red-600">
                                        {{ form.errors.ordre }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Détermine l'ordre d'affichage dans les listes
                                    </p>
                                </div>

                                <!-- Code -->
                                <div class="space-y-2">
                                    <Label for="code" class="text-sm font-medium">
                                        Code <span class="text-red-500">*</span>
                                    </Label>
                                    <div class="relative">
                                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                            <Hash class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <Input
                                            id="code"
                                            type="text"
                                            v-model="form.code"
                                            placeholder="Ex: 01, GO, SO..."
                                            required
                                            maxlength="10"
                                            class="pl-10 w-full font-mono"
                                            :class="{ 'border-red-300': form.errors.code }"
                                        />
                                    </div>
                                    <p v-if="form.errors.code" class="text-sm text-red-600">
                                        {{ form.errors.code }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Code court identifiant le corps d'état
                                    </p>
                                </div>

                                <!-- Intitulé -->
                                <div class="space-y-2">
                                    <Label for="intitule" class="text-sm font-medium">
                                        Intitulé <span class="text-red-500">*</span>
                                    </Label>
                                    <div class="relative">
                                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                            <Briefcase class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <Input
                                            id="intitule"
                                            type="text"
                                            v-model="form.intitule"
                                            placeholder="Ex: GROS-ŒUVRE, SECOND-ŒUVRE, ÉQUIPEMENT"
                                            required
                                            class="pl-10 w-full"
                                            :class="{ 'border-red-300': form.errors.intitule }"
                                        />
                                    </div>
                                    <p v-if="form.errors.intitule" class="text-sm text-red-600">
                                        {{ form.errors.intitule }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Nom complet du corps d'état
                                    </p>
                                </div>

                                <!-- Sous-total -->
                                <div class="space-y-2">
                                    <Label for="sous_total" class="text-sm font-medium">
                                        Sous-total estimé
                                    </Label>
                                    <div class="relative">
                                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                            <DollarSign class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <Input
                                            id="sous_total"
                                            type="number"
                                            v-model="form.sous_total"
                                            placeholder="Ex: 1000000"
                                            min="0"
                                            step="0.01"
                                            class="pl-10 w-full"
                                            :class="{ 'border-red-300': form.errors.sous_total }"
                                        />
                                    </div>
                                    <p v-if="form.errors.sous_total" class="text-sm text-red-600">
                                        {{ form.errors.sous_total }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Montant estimé pour cette catégorie (optionnel)
                                    </p>

                                    <!-- Aperçu du montant formaté -->
                                    <div v-if="form.sous_total" class="mt-2 p-3 bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/10 dark:to-green-800/10 border border-green-200 dark:border-green-800 rounded-lg">
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Aperçu :</div>
                                        <div class="text-xl font-bold text-green-700 dark:text-green-400">
                                            {{ formatNumber(form.sous_total) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
                                    <Link
                                        :href="route('corps-etat.index')"
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
                                                Corps d'état créé avec succès !
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
                                                <Building class="w-4 h-4 mr-2" />
                                                Créer le corps d'état
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
import { ArrowLeft, Loader2, Building, ListOrdered, Hash, Briefcase, DollarSign } from 'lucide-vue-next'
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

const currentPageTitle = ref("Nouveau Corps d'État")

const form = useForm({
    code: '',
    intitule: '',
    ordre: 1,
    sous_total: 0
})

const formatNumber = (value) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value || 0)
}
</script>
