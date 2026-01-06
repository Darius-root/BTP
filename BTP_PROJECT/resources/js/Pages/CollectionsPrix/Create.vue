<template>
    <Head title="Nouveau Prix" />

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
                                    <CardTitle class="text-3xl">Ajouter un prix</CardTitle>
                                    <CardDescription class="mt-1">
                                        Enregistrez un nouveau prix pour un matériau dans une localité spécifique
                                    </CardDescription>
                                </div>
                                <Link
                                    :href="route('collections-prix.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors"
                                >
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="form.post(route('collections-prix.store'))" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Colonne gauche : Localisation -->
                                    <div class="space-y-6">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b pb-2">
                                            <MapPin class="w-5 h-5 inline-block mr-2" />
                                            Localisation
                                        </h3>

                                        <!-- Commune -->
                                        <div class="space-y-2">
                                            <Label for="commune_id" class="text-sm font-medium">
                                                Commune <span class="text-red-500">*</span>
                                            </Label>
                                            <select
                                                id="commune_id"
                                                v-model="form.commune_id"
                                                @change="loadArrondissements"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                :class="{ 'border-red-300': form.errors.commune_id }"
                                            >
                                                <option value="">Sélectionnez une commune</option>
                                                <option
                                                    v-for="commune in communes"
                                                    :key="commune.id"
                                                    :value="commune.id"
                                                >
                                                    {{ commune.libelle }} ({{ commune.code }})
                                                </option>
                                            </select>
                                            <p v-if="form.errors.commune_id" class="text-sm text-red-600">
                                                {{ form.errors.commune_id }}
                                            </p>
                                        </div>

                                        <!-- Arrondissement -->
                                        <div class="space-y-2">
                                            <Label for="arrondissement_id" class="text-sm font-medium">
                                                Arrondissement
                                            </Label>
                                            <select
                                                id="arrondissement_id"
                                                v-model="form.arrondissement_id"
                                                :disabled="!form.commune_id || arrondissements.length === 0"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                :class="{
                                                    'border-red-300': form.errors.arrondissement_id,
                                                    'opacity-50 cursor-not-allowed': !form.commune_id || arrondissements.length === 0
                                                }"
                                            >
                                                <option value="">Sélectionnez un arrondissement</option>
                                                <option
                                                    v-for="arrondissement in arrondissements"
                                                    :key="arrondissement.id"
                                                    :value="arrondissement.id"
                                                >
                                                    {{ arrondissement.libelle }}
                                                </option>
                                            </select>
                                            <p v-if="form.errors.arrondissement_id" class="text-sm text-red-600">
                                                {{ form.errors.arrondissement_id }}
                                            </p>
                                            <p v-if="form.commune_id && arrondissements.length === 0" class="text-xs text-gray-500">
                                                Cette commune n'a pas d'arrondissements
                                            </p>
                                        </div>

                                        <!-- Quartier -->
                                        <div class="space-y-2">
                                            <Label for="quartier_id" class="text-sm font-medium">
                                                Quartier / Zone
                                            </Label>
                                            <Input
                                                id="quartier_id"
                                                type="text"
                                                v-model="form.quartier_id"
                                                placeholder="Ex: Quartier Gbégamey, Zone Industrielle..."
                                                class="w-full"
                                                :class="{ 'border-red-300': form.errors.quartier_id }"
                                            />
                                            <p v-if="form.errors.quartier_id" class="text-sm text-red-600">
                                                {{ form.errors.quartier_id }}
                                            </p>
                                        </div>

                                        <!-- Point de vente -->
                                        <div class="space-y-2">
                                            <Label for="point_vente" class="text-sm font-medium">
                                                Point de vente
                                            </Label>
                                            <Input
                                                id="point_vente"
                                                type="text"
                                                v-model="form.point_vente"
                                                placeholder="Ex: Marché Dantokpa, Magasin XYZ..."
                                                class="w-full"
                                                :class="{ 'border-red-300': form.errors.point_vente }"
                                            />
                                            <p v-if="form.errors.point_vente" class="text-sm text-red-600">
                                                {{ form.errors.point_vente }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Colonne droite : Matériau et prix -->
                                    <div class="space-y-6">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b pb-2">
                                            <Package class="w-5 h-5 inline-block mr-2" />
                                            Matériau et Prix
                                        </h3>

                                        <!-- Catégorie -->
                                        <div class="space-y-2">
                                            <Label for="categorie_id" class="text-sm font-medium">
                                                Catégorie <span class="text-red-500">*</span>
                                            </Label>
                                            <select
                                                id="categorie_id"
                                                v-model="form.categorie_id"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                :class="{ 'border-red-300': form.errors.categorie_id }"
                                            >
                                                <option value="">Sélectionnez une catégorie</option>
                                                <option
                                                    v-for="categorie in categories"
                                                    :key="categorie.id"
                                                    :value="categorie.id"
                                                >
                                                    {{ categorie.intitule }} ({{ categorie.code }})
                                                </option>
                                            </select>
                                            <p v-if="form.errors.categorie_id" class="text-sm text-red-600">
                                                {{ form.errors.categorie_id }}
                                            </p>
                                        </div>

                                        <!-- Matériau -->
                                        <div class="space-y-2">
                                            <Label for="materiau_id" class="text-sm font-medium">
                                                Matériau <span class="text-red-500">*</span>
                                            </Label>
                                            <select
                                                id="materiau_id"
                                                v-model="form.materiau_id"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                :class="{ 'border-red-300': form.errors.materiau_id }"
                                            >
                                                <option value="">Sélectionnez un matériau</option>
                                                <option
                                                    v-for="materiau in materiaux"
                                                    :key="materiau.id"
                                                    :value="materiau.id"
                                                >
                                                    {{ materiau.nom }} ({{ materiau.code }}) - {{ materiau.unite.libelle }}
                                                </option>
                                            </select>
                                            <p v-if="form.errors.materiau_id" class="text-sm text-red-600">
                                                {{ form.errors.materiau_id }}
                                            </p>
                                        </div>

                                        <!-- Description -->
                                        <div class="space-y-2">
                                            <Label for="description_materiaux" class="text-sm font-medium">
                                                Description <span class="text-red-500">*</span>
                                            </Label>
                                            <textarea
                                                id="description_materiaux"
                                                v-model="form.description_materiaux"
                                                placeholder="Décrivez le matériau, ses caractéristiques..."
                                                required
                                                rows="3"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                :class="{ 'border-red-300': form.errors.description_materiaux }"
                                            ></textarea>
                                            <p v-if="form.errors.description_materiaux" class="text-sm text-red-600">
                                                {{ form.errors.description_materiaux }}
                                            </p>
                                        </div>

                                        <!-- Détails supplémentaires -->
                                        <div class="space-y-2">
                                            <Label for="detail" class="text-sm font-medium">
                                                Détails supplémentaires
                                            </Label>
                                            <textarea
                                                id="detail"
                                                v-model="form.detail"
                                                placeholder="Informations complémentaires, conditions de vente..."
                                                rows="2"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                :class="{ 'border-red-300': form.errors.detail }"
                                            ></textarea>
                                            <p v-if="form.errors.detail" class="text-sm text-red-600">
                                                {{ form.errors.detail }}
                                            </p>
                                        </div>

                                        <!-- Prix et Devise -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="price" class="text-sm font-medium">
                                                    Prix <span class="text-red-500">*</span>
                                                </Label>
                                                <Input
                                                    id="price"
                                                    type="number"
                                                    v-model="form.price"
                                                    placeholder="0.00"
                                                    required
                                                    min="0"
                                                    step="0.01"
                                                    class="w-full"
                                                    :class="{ 'border-red-300': form.errors.price }"
                                                />
                                                <p v-if="form.errors.price" class="text-sm text-red-600">
                                                    {{ form.errors.price }}
                                                </p>
                                            </div>

                                            <div class="space-y-2">
                                                <Label for="devise_id" class="text-sm font-medium">
                                                    Devise <span class="text-red-500">*</span>
                                                </Label>
                                                <select
                                                    id="devise_id"
                                                    v-model="form.devise_id"
                                                    required
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                    :class="{ 'border-red-300': form.errors.devise_id }"
                                                >
                                                    <option value="">Sélectionnez une devise</option>
                                                    <option
                                                        v-for="devise in devises"
                                                        :key="devise.id"
                                                        :value="devise.id"
                                                    >
                                                        {{ devise.libelle }} ({{ devise.symbole || devise.code }})
                                                    </option>
                                                </select>
                                                <p v-if="form.errors.devise_id" class="text-sm text-red-600">
                                                    {{ form.errors.devise_id }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Aperçu du prix -->
                                        <div v-if="form.price && form.devise_id" class="p-4 bg-gradient-to-r from-green-50 to-emerald-100 dark:from-green-900/10 dark:to-emerald-900/10 border border-green-200 dark:border-green-800 rounded-lg">
                                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Aperçu du prix :</div>
                                            <div class="text-2xl font-bold text-green-700 dark:text-green-400">
                                                {{ formatNumber(form.price) }}
                                                <span class="text-lg">
                                                    {{ getDeviseSymbol(form.devise_id) }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                                Par {{ getSelectedMaterialUnit() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <Link
                                        :href="route('collections-prix.index')"
                                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
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
                                                Prix ajouté avec succès !
                                            </p>
                                        </Transition>
                                        <Button
                                            type="submit"
                                            :disabled="form.processing"
                                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 px-6"
                                        >
                                            <span v-if="form.processing" class="flex items-center">
                                                <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                                                Enregistrement...
                                            </span>
                                            <span v-else class="flex items-center">
                                                <Plus class="w-4 h-4 mr-2" />
                                                Ajouter le prix
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
import { ref, computed } from 'vue'
import { useForm, Link, Head } from '@inertiajs/vue3'
import { ArrowLeft, Loader2, Plus, MapPin, Package } from 'lucide-vue-next'
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

const currentPageTitle = ref("Nouveau Prix")

const props = defineProps({
    communes: {
        type: Array,
        required: true,
    },
    materiaux: {
        type: Array,
        required: true,
    },
    devises: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    }
})

const form = useForm({
    commune_id: '',
    arrondissement_id: '',
    quartier_id: '',
    materiau_id: '',
    devise_id: '',
    categorie_id: '',
    description_materiaux: '',
    detail: '',
    price: '',
    point_vente: '',
})

const arrondissements = ref([])

const loadArrondissements = async () => {
    if (!form.commune_id) {
        arrondissements.value = []
        return
    }

    try {
        const response = await fetch(route('collections-prix.arrondissements', form.commune_id))
        const data = await response.json()
        arrondissements.value = data
    } catch (error) {
        console.error('Erreur lors du chargement des arrondissements:', error)
        arrondissements.value = []
    }
}

const formatNumber = (value) => {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0)
}

const getDeviseSymbol = (deviseId) => {
    const devise = props.devises.find(d => d.id == deviseId)
    return devise ? (devise.symbole || devise.code) : ''
}

const getSelectedMaterialUnit = () => {
    const materiau = props.materiaux.find(m => m.id == form.materiau_id)
    return materiau ? materiau.unite.libelle : 'unité'
}
</script>
