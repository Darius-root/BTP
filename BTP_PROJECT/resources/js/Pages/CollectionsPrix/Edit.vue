<<<<<<< HEAD
<template>

    <Head :title="pageTitle" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <!-- Card du formulaire -->
                <div
                    class="rounded-2xl border  p-5  lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Modifier le prix</CardTitle>
                                    <CardDescription class="mt-1">
                                        Mettez à jour les informations du prix
                                        <span v-if="selectedMaterial"
                                            class="block text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">
                                            {{ selectedMaterial.nom }} ({{ selectedMaterial.unite?.libelle || 'N/A' }})
                                        </span>
                                        <!-- Indication du statut de validation -->
                                        <div v-if="!collection.is_validated" class="mt-2">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Non validée
                                            </span>
                                        </div>
                                    </CardDescription>
                                </div>
                                <Link :href="route('collections-prix.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="form.put(route('collections-prix.update', collection.id))"
                                class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Colonne gauche : Localisation -->
                                    <div class="space-y-6">
                                        <h3
                                            class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b pb-2">
                                            <MapPin class="w-5 h-5 inline-block mr-2" />
                                            Localisation
                                        </h3>

                                        <!-- Commune -->
                                        <div class="space-y-2">
                                            <Label for="commune_id" class="text-sm font-medium">
                                                Commune <span class="text-red-500">*</span>
                                            </Label>
                                            <select id="commune_id" v-model="form.commune_id" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                :class="{ 'border-red-300': errors.commune_id }">
                                                <option value="">Sélectionnez une commune</option>
                                                <option v-for="commune in communes" :key="commune.id"
                                                    :value="commune.id">
                                                    {{ commune.libelle }} ({{ commune.code }})
                                                </option>
                                            </select>
                                            <p v-if="errors.commune_id" class="text-sm text-red-600">
                                                {{ errors.commune_id }}
                                            </p>
                                        </div>

                                        <!-- Arrondissement -->
                                        <div class="space-y-2">
                                            <Label for="arrondissement_id" class="text-sm font-medium">
                                                Arrondissement
                                            </Label>
                                            <select id="arrondissement_id" v-model="form.arrondissement_id"
                                                :disabled="!form.commune_id"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                :class="{
                                                    'border-red-300': errors.arrondissement_id,
                                                    'opacity-50 cursor-not-allowed': !form.commune_id
                                                }">
                                                <option value="">Sélectionnez un arrondissement</option>
                                                <option v-for="arrondissement in arrondissements"
                                                    :key="arrondissement.id" :value="arrondissement.id">
                                                    {{ arrondissement.libelle }}
                                                </option>
                                            </select>
                                            <p v-if="errors.arrondissement_id" class="text-sm text-red-600">
                                                {{ errors.arrondissement_id }}
                                            </p>
                                            <p v-if="form.commune_id && arrondissements.length === 0"
                                                class="text-xs text-gray-500">
                                                Chargement des arrondissements...
                                            </p>
                                        </div>

                                        <!-- Quartier -->
                                        <div class="space-y-2">
                                            <Label for="quartier_id" class="text-sm font-medium">
                                                Quartier / Zone
                                            </Label>
                                            <Input id="quartier_id" type="text" v-model="form.quartier_id"
                                                placeholder="Ex: Quartier Gbégamey, Zone Industrielle..." class="w-full"
                                                :class="{ 'border-red-300': errors.quartier_id }" />
                                            <p v-if="errors.quartier_id" class="text-sm text-red-600">
                                                {{ errors.quartier_id }}
                                            </p>
                                        </div>

                                        <!-- Point de vente -->
                                        <div class="space-y-2">
                                            <Label for="point_vente" class="text-sm font-medium">
                                                Point de vente
                                            </Label>
                                            <Input id="point_vente" type="text" v-model="form.point_vente"
                                                placeholder="Ex: Marché Dantokpa, Magasin XYZ..." class="w-full"
                                                :class="{ 'border-red-300': errors.point_vente }" />
                                            <p v-if="errors.point_vente" class="text-sm text-red-600">
                                                {{ errors.point_vente }}
                                            </p>
                                        </div>

                                        <!-- Détails supplémentaires -->
                                        <div class="space-y-2">
                                            <Label for="detail" class="text-sm font-medium">
                                                Détails supplémentaires
                                            </Label>
                                            <textarea id="detail" v-model="form.detail"
                                                placeholder="Informations complémentaires, conditions de vente..."
                                                rows="2"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                :class="{ 'border-red-300': errors.detail }"></textarea>
                                            <p v-if="errors.detail" class="text-sm text-red-600">
                                                {{ errors.detail }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Colonne droite : Matériau et prix -->
                                    <div class="space-y-6">
                                        <h3
                                            class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b pb-2">
                                            <Package class="w-5 h-5 inline-block mr-2" />
                                            Matériel et Prix
                                        </h3>

                                        <!-- Catégorie -->
                                        <div class="space-y-2">
                                            <Label for="categorie_id" class="text-sm font-medium">
                                                Catégorie <span class="text-red-500">*</span>
                                            </Label>
                                            <select id="categorie_id" v-model="form.categorie_id" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                :class="{ 'border-red-300': errors.categorie_id }">
                                                <option value="">Sélectionnez une catégorie</option>
                                                <option v-for="categorie in categories" :key="categorie.id"
                                                    :value="categorie.id">
                                                    {{ categorie.intitule }} ({{ categorie.code }})
                                                </option>
                                            </select>
                                            <p v-if="errors.categorie_id" class="text-sm text-red-600">
                                                {{ errors.categorie_id }}
                                            </p>
                                        </div>

                                        <!-- Matériau -->
                                        <div class="space-y-2">
                                            <Label for="materiau_id" class="text-sm font-medium">
                                                Matériel <span class="text-red-500">*</span>
                                            </Label>
                                            <select id="materiau_id" v-model="form.materiau_id" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                :class="{ 'border-red-300': errors.materiau_id }">
                                                <option value="">Sélectionnez un matériel</option>
                                                <option v-for="materiau in materiaux" :key="materiau.id"
                                                    :value="materiau.id">
                                                    {{ materiau.nom }} ({{ materiau.code }}) - {{
                                                    materiau.unite?.libelle || 'N/A' }}
                                                </option>
                                            </select>
                                            <p v-if="errors.materiau_id" class="text-sm text-red-600">
                                                {{ errors.materiau_id }}
                                            </p>
                                        </div>

                                        <!-- Description -->
                                        <div class="space-y-2">
                                            <Label for="description_materiaux" class="text-sm font-medium">
                                                Description <span class="text-red-500">*</span>
                                            </Label>
                                            <textarea id="description_materiaux" v-model="form.description_materiaux"
                                                placeholder="Décrivez le matériel, ses caractéristiques..." required
                                                rows="3"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                :class="{ 'border-red-300': errors.description_materiaux }"></textarea>
                                            <p v-if="errors.description_materiaux" class="text-sm text-red-600">
                                                {{ errors.description_materiaux }}
                                            </p>
                                        </div>

                                        <!-- Prix et Devise -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="price" class="text-sm font-medium">
                                                    Prix <span class="text-red-500">*</span>
                                                </Label>
                                                <Input id="price" type="number" v-model="form.price" placeholder="0.00"
                                                    required min="0" step="0.01" class="w-full"
                                                    :class="{ 'border-red-300': errors.price }" />
                                                <p v-if="errors.price" class="text-sm text-red-600">
                                                    {{ errors.price }}
                                                </p>
                                            </div>

                                            <div class="space-y-2">
                                                <Label for="devise_id" class="text-sm font-medium">
                                                    Devise <span class="text-red-500">*</span>
                                                </Label>
                                                <select id="devise_id" v-model="form.devise_id" required
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                    :class="{ 'border-red-300': errors.devise_id }">
                                                    <option value="">Sélectionnez une devise</option>
                                                    <option v-for="devise in devises" :key="devise.id"
                                                        :value="devise.id">
                                                        {{ devise.libelle }} ({{ devise.symbole || devise.code }})
                                                    </option>
                                                </select>
                                                <p v-if="errors.devise_id" class="text-sm text-red-600">
                                                    {{ errors.devise_id }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Section Status supprimée -->
                                        <!-- Aperçu du prix -->
                                        <div
                                            class="p-4 bg-gradient-to-r from-green-50 to-emerald-100 dark:from-green-900/10 dark:to-emerald-900/10 border border-green-200 dark:border-green-800 rounded-lg">
                                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Aperçu du prix :
                                            </div>
                                            <div class="text-2xl font-bold text-green-700 dark:text-green-400">
                                                {{ formatNumber(form.price) }}
                                                <span class="text-lg">
                                                    {{ getDeviseSymbol() }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                                Par {{ getSelectedMaterialUnit() }}
                                            </div>
                                            <!-- Affichage du statut de validation -->
                                            <div class="mt-3 pt-3 border-t border-green-200 dark:border-green-700">
                                                <div class="flex items-center gap-2">
                                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                                        Statut de validation:
                                                    </div>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                        :class="collection.is_validated
                                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'">
                                                        <svg v-if="collection.is_validated" class="w-3 h-3 mr-1"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        <svg v-else class="w-3 h-3 mr-1" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                            </path>
                                                        </svg>
                                                        {{ collection.is_validated ? 'Validée' : 'Non validée' }}
                                                    </span>
                                                </div>
                                                <p v-if="!collection.is_validated"
                                                    class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                                    Cette collecte doit être validée par un administrateur
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations sur la création -->
                                <div v-if="collection.user || collection.created_at"
                                    class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div v-if="collection.user">
                                            <div class="text-gray-500 dark:text-gray-400">Créé par</div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ collection.user.name || 'Non spécifié' }}
                                            </div>
                                        </div>
                                        <div v-if="collection.created_at">
                                            <div class="text-gray-500 dark:text-gray-400">Date de création</div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ formatDate(collection.created_at) }}
                                            </div>
                                        </div>
                                        <div v-if="collection.updated_at">
                                            <div class="text-gray-500 dark:text-gray-400">Dernière mise à jour</div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ formatDate(collection.updated_at) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div
                                    class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center gap-4">
                                        <button type="button" @click="confirmDelete"
                                            class="text-sm text-red-600 hover:text-red-800 dark:hover:text-red-400 transition-colors">
                                            <Trash2 class="w-4 h-4 inline mr-1" />
                                            Supprimer ce prix
                                        </button>
                                        <Link :href="route('collections-prix.index')"
                                            class="text-sm text-gray-600 hover:text-gray-900 dark:hover:text-gray-300 transition-colors">
                                            Annuler
                                        </Link>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <Transition enter-active-class="transition-opacity duration-300"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition-opacity duration-300"
                                            leave-to-class="opacity-0">
                                            <p v-if="form.recentlySuccessful"
                                                class="text-sm text-green-600 dark:text-green-400">
                                                Prix mis à jour avec succès !
                                            </p>
                                        </Transition>
                                        <Button type="submit" :disabled="form.processing"
                                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 px-6">
                                            <span v-if="form.processing" class="flex items-center">
                                                <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                                                Mise à jour...
                                            </span>
                                            <span v-else class="flex items-center">
                                                <Save class="w-4 h-4 mr-2" />
                                                Mettre à jour
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

=======
>>>>>>> dev_emmanuel
<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useForm, Link, Head, router } from '@inertiajs/vue3'
import { ArrowLeft, Loader2, Save, Trash2, MapPin, Package } from 'lucide-vue-next'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

const currentPageTitle = ref("Modifier Prix")

const props = defineProps({
    collection: { type: Object, required: true },
    communes: { type: Array, required: true },
    materiaux: { type: Array, required: true },
    unites: { type: Array, required: true },
    devises: { type: Array, required: true },
    categories: { type: Array, required: true },
    errors: { type: Object, default: () => ({}) }
})

// Initialiser le formulaire
const form = useForm({
    commune_id: props.collection.commune_id || '',
    arrondissement_id: props.collection.arrondissement_id || '',
    quartier: props.collection.quartier || '',
    materiau_id: props.collection.materiau_id || '',
    unite_id: props.collection.unite_id || '',
    devise_id: props.collection.devise_id || '',
    categorie_id: props.collection.categorie_id || '',
    description_materiaux: props.collection.description_materiaux || '',
    detail: props.collection.detail || '',
    price: props.collection.price || 0,
    point_vente: props.collection.point_vente || '',
})

const arrondissements = ref([])

// Computed properties
const selectedMaterial = computed(() =>
    props.materiaux.find(m => m.id == form.materiau_id) || null
)

const selectedDevise = computed(() =>
    props.devises.find(d => d.id == form.devise_id) || null
)

const selectedUnite = computed(() =>
    props.unites.find(u => u.id == form.unite_id) || null
)

const pageTitle = computed(() => {
    if (selectedMaterial.value) {
        return `Modifier Prix - ${selectedMaterial.value.nom}`
    }
    return 'Modifier Prix'
})

// Charger les arrondissements
const loadArrondissements = async () => {
    if (!form.commune_id) {
        arrondissements.value = []
        return
    }

    try {
        const response = await fetch(route('collections-prix.arrondissements', form.commune_id))
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`)
        const data = await response.json()
        arrondissements.value = data
    } catch (error) {
        console.error('Erreur lors du chargement des arrondissements:', error)
        arrondissements.value = []
    }
}

onMounted(() => {
    if (form.commune_id) loadArrondissements()
})

watch(() => form.commune_id, (newCommuneId) => {
    if (newCommuneId) {
        loadArrondissements()
    } else {
        arrondissements.value = []
    }
})

const getMaterialInfo = () => {
    return selectedMaterial.value ? selectedMaterial.value.nom : 'Matériel inconnu'
}

const getLocationInfo = () => {
    const commune = props.communes.find(c => c.id == form.commune_id)
    let location = commune ? commune.libelle : ''

    if (form.arrondissement_id) {
        const arrondissement = arrondissements.value.find(a => a.id == form.arrondissement_id)
        if (arrondissement) location += ` - ${arrondissement.libelle}`
    }

    if (form.quartier) location += ` (${form.quartier})`

    return location || 'Localisation inconnue'
}

const confirmDelete = () => {
    const materialInfo = getMaterialInfo()
    const locationInfo = getLocationInfo()

    if (confirm(`Êtes-vous sûr de vouloir supprimer définitivement le prix pour "${materialInfo}" à "${locationInfo}" ? Cette action est irréversible.`)) {
        router.delete(route('collections-prix.destroy', props.collection.id))
    }
}

const formatNumber = (value) => {
    const num = parseFloat(value)
    if (isNaN(num)) return '0,00'
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num)
}

const formatDate = (dateString) => {
    if (!dateString) return 'Non spécifié'
    try {
        const date = new Date(dateString)
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        })
    } catch {
        return 'Date invalide'
    }
}

const getDeviseSymbol = () => {
    return selectedDevise.value ? (selectedDevise.value.symbole || selectedDevise.value.code) : ''
}

const getSelectedMaterialUnit = () => {
    return selectedUnite.value ? selectedUnite.value.libelle : 'unité'
}
</script>

<template>

    <Head :title="pageTitle" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Modifier le prix</CardTitle>
                                    <CardDescription class="mt-1">
                                        Mettez à jour les informations du prix
                                        <span v-if="selectedMaterial"
                                            class="block text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">
                                            {{ selectedMaterial.nom }}
                                        </span>
                                        <div v-if="!collection.is_validated" class="mt-2">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Non validée
                                            </span>
                                        </div>
                                    </CardDescription>
                                </div>
                                <Link :href="route('collections-prix.index')"
                                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                                    <ArrowLeft class="w-4 h-4 mr-2" />
                                    Retour à la liste
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <form @submit.prevent="form.put(route('collections-prix.update', collection.id))"
                                class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Colonne gauche : Localisation -->
                                    <div class="space-y-6">
                                        <h3
                                            class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b pb-2">
                                            <MapPin class="w-5 h-5 inline-block mr-2" />
                                            Localisation
                                        </h3>

                                        <!-- Commune -->
                                        <div class="space-y-2">
                                            <Label for="commune_id" class="text-sm font-medium">
                                                Commune <span class="text-red-500">*</span>
                                            </Label>
                                            <select id="commune_id" v-model="form.commune_id" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                :class="{ 'border-red-300': errors.commune_id }">
                                                <option value="">Sélectionnez une commune</option>
                                                <option v-for="commune in communes" :key="commune.id"
                                                    :value="commune.id">
                                                    {{ commune.libelle }}
                                                </option>
                                            </select>
                                            <p v-if="errors.commune_id" class="text-sm text-red-600">
                                                {{ errors.commune_id }}
                                            </p>
                                        </div>

                                        <!-- Arrondissement -->
                                        <div class="space-y-2">
                                            <Label for="arrondissement_id" class="text-sm font-medium">
                                                Arrondissement
                                            </Label>
                                            <select id="arrondissement_id" v-model="form.arrondissement_id"
                                                :disabled="!form.commune_id"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                                :class="{
                                                    'border-red-300': errors.arrondissement_id,
                                                    'opacity-50 cursor-not-allowed': !form.commune_id
                                                }">
                                                <option value="">Sélectionnez un arrondissement</option>
                                                <option v-for="arrondissement in arrondissements"
                                                    :key="arrondissement.id" :value="arrondissement.id">
                                                    {{ arrondissement.libelle }}
                                                </option>
                                            </select>
                                            <p v-if="errors.arrondissement_id" class="text-sm text-red-600">
                                                {{ errors.arrondissement_id }}
                                            </p>
                                        </div>

                                        <!-- Quartier -->
                                        <div class="space-y-2">
                                            <Label for="quartier" class="text-sm font-medium">
                                                Quartier / Zone
                                            </Label>
                                            <Input id="quartier" type="text" v-model="form.quartier"
                                                placeholder="Ex: Quartier Gbégamey..." class="w-full"
                                                :class="{ 'border-red-300': errors.quartier }" />
                                        </div>

                                        <!-- Point de vente -->
                                        <div class="space-y-2">
                                            <Label for="point_vente" class="text-sm font-medium">
                                                Point de vente
                                            </Label>
                                            <Input id="point_vente" type="text" v-model="form.point_vente"
                                                placeholder="Ex: Marché Dantokpa..." class="w-full" />
                                        </div>

                                        <!-- Détails -->
                                        <div class="space-y-2">
                                            <Label for="detail" class="text-sm font-medium">
                                                Détails supplémentaires
                                            </Label>
                                            <textarea id="detail" v-model="form.detail"
                                                placeholder="Informations complémentaires..." rows="2"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700"></textarea>
                                        </div>
                                    </div>

                                    <!-- Colonne droite : Matériau et prix -->
                                    <div class="space-y-6">
                                        <h3
                                            class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b pb-2">
                                            <Package class="w-5 h-5 inline-block mr-2" />
                                            Matériel et Prix
                                        </h3>

                                        <!-- Catégorie -->
                                        <div class="space-y-2">
                                            <Label for="categorie_id" class="text-sm font-medium">
                                                Catégorie <span class="text-red-500">*</span>
                                            </Label>
                                            <select id="categorie_id" v-model="form.categorie_id" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700">
                                                <option value="">Sélectionnez une catégorie</option>
                                                <option v-for="categorie in categories" :key="categorie.id"
                                                    :value="categorie.id">
                                                    {{ categorie.intitule }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Matériau -->
                                        <div class="space-y-2">
                                            <Label for="materiau_id" class="text-sm font-medium">
                                                Matériel <span class="text-red-500">*</span>
                                            </Label>
                                            <select id="materiau_id" v-model="form.materiau_id" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700">
                                                <option value="">Sélectionnez un matériel</option>
                                                <option v-for="materiau in materiaux" :key="materiau.id"
                                                    :value="materiau.id">
                                                    {{ materiau.nom }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Unité -->
                                        <div class="space-y-2">
                                            <Label for="unite_id" class="text-sm font-medium">
                                                Unité de mesure <span class="text-red-500">*</span>
                                            </Label>
                                            <select id="unite_id" v-model="form.unite_id" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700">
                                                <option value="">Sélectionnez une unité</option>
                                                <option v-for="unite in unites" :key="unite.id" :value="unite.id">
                                                    {{ unite.libelle }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Description -->
                                        <div class="space-y-2">
                                            <Label for="description_materiaux" class="text-sm font-medium">
                                                Description <span class="text-red-500">*</span>
                                            </Label>
                                            <textarea id="description_materiaux" v-model="form.description_materiaux"
                                                required rows="3"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700"></textarea>
                                        </div>

                                        <!-- Prix et Devise -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="price">Prix <span class="text-red-500">*</span></Label>
                                                <Input id="price" type="number" v-model="form.price" required min="0"
                                                    step="0.01" />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="devise_id">Devise <span
                                                        class="text-red-500">*</span></Label>
                                                <select id="devise_id" v-model="form.devise_id" required
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700">
                                                    <option value="">Sélectionnez</option>
                                                    <option v-for="devise in devises" :key="devise.id"
                                                        :value="devise.id">
                                                        {{ devise.libelle }} ({{ devise.symbole || devise.code }})
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Aperçu -->
                                        <div
                                            class="p-4 bg-gradient-to-r from-green-50 to-emerald-100 dark:from-green-900/10 dark:to-emerald-900/10 border border-green-200 dark:border-green-800 rounded-lg">
                                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Aperçu :</div>
                                            <div class="text-2xl font-bold text-green-700 dark:text-green-400">
                                                {{ formatNumber(form.price) }}
                                                <span class="text-lg">{{ getDeviseSymbol() }}</span>
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                                Par {{ getSelectedMaterialUnit() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Infos création -->
                                <div v-if="collection.user || collection.created_at"
                                    class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div v-if="collection.user">
                                            <div class="text-gray-500 dark:text-gray-400">Créé par</div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ collection.user.name || 'Non spécifié' }}
                                            </div>
                                        </div>
                                        <div v-if="collection.created_at">
                                            <div class="text-gray-500 dark:text-gray-400">Date de création</div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ formatDate(collection.created_at) }}
                                            </div>
                                        </div>
                                        <div v-if="collection.updated_at">
                                            <div class="text-gray-500 dark:text-gray-400">Dernière mise à jour</div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ formatDate(collection.updated_at) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div
                                    class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center gap-4">
                                        <button type="button" @click="confirmDelete"
                                            class="text-sm text-red-600 hover:text-red-800 transition-colors">
                                            <Trash2 class="w-4 h-4 inline mr-1" />
                                            Supprimer ce prix
                                        </button>
                                        <Link :href="route('collections-prix.index')"
                                            class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                                            Annuler
                                        </Link>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <Transition enter-active-class="transition-opacity duration-300"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition-opacity duration-300"
                                            leave-to-class="opacity-0">
                                            <p v-if="form.recentlySuccessful"
                                                class="text-sm text-green-600 dark:text-green-400">
                                                Prix mis à jour !
                                            </p>
                                        </Transition>
                                        <Button type="submit" :disabled="form.processing"
                                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 px-6">
                                            <span v-if="form.processing" class="flex items-center">
                                                <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                                                Mise à jour...
                                            </span>
                                            <span v-else class="flex items-center">
                                                <Save class="w-4 h-4 mr-2" />
                                                Mettre à jour
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
