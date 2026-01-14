<template>
    <Head title="Détails du Prix" />
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
                                    <CardTitle class="text-3xl">Détails du Prix</CardTitle>
                                    <CardDescription class="mt-1">
                                        Visualisez toutes les informations concernant ce prix pour un matériel.
                                    </CardDescription>
                                </div>

                                <div class="flex gap-2">
                                    <Link :href="route('collections-prix.index')"
                                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                        <ArrowLeft class="w-4 h-4 mr-2" /> Retour à la liste
                                    </Link>

                                    <template v-if="!collection.is_validated || isAdmin">
                                        <Link v-if="canEdit()" :href="route('collections-prix.edit', collection.id)"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                            <Edit class="w-4 h-4 mr-2" /> Modifier
                                        </Link>

                                        <button v-if="canDelete()" @click="openDeleteDialog()"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
                                            <Trash2 class="w-4 h-4 mr-2" /> Supprimer
                                        </button>

                                        <button v-if="canValidate()" @click="openValidateDialog(collection)"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                                            <CheckCircle class="w-4 h-4 mr-2" /> Valider la collecte
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b pb-2">
                                        <MapPin class="w-5 h-5 inline-block mr-2" /> Localisation
                                    </h3>

                                    <div>
                                        <Label>Commune</Label>
                                        <p class="font-medium text-gray-900 dark:text-white">{{
                                            collection.commune?.libelle || '-' }}</p>
                                    </div>
                                    <div>
                                        <Label>Arrondissement</Label>
                                        <p class="font-medium text-gray-900 dark:text-white">{{
                                            collection.arrondissement?.libelle || '-' }}</p>
                                    </div>
                                    <div>
                                        <Label>Quartier / Zone</Label>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ collection.quartier_id
                                            || '-' }}</p>
                                    </div>
                                    <div>
                                        <Label>Point de vente</Label>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ collection.point_vente
                                            || '-' }}</p>
                                    </div>
                                    <div>
                                        <Label>Détails supplémentaires</Label>
                                        <p class="text-gray-900 dark:text-white">{{ collection.detail || '-' }}</p>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b pb-2">
                                        <Package class="w-5 h-5 inline-block mr-2" /> Matériel et Prix
                                    </h3>

                                    <div>
                                        <Label>Corps d'état</Label>
                                        <p class="font-medium text-gray-900 dark:text-white">{{
                                            collection.categorie?.intitule || '-' }}</p>
                                    </div>
                                    <div>
                                        <Label>Matériel</Label>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ collection.materiau?.nom || '-' }}
                                            <span v-if="collection.materiau?.unite"
                                                class="text-sm text-gray-600 dark:text-gray-400 ml-1">
                                                ({{ collection.materiau.unite.libelle || collection.materiau.unite.nom
                                                || '-' }})
                                            </span>
                                        </p>
                                    </div>
                                    <div>
                                        <Label>Description</Label>
                                        <p class="text-gray-900 dark:text-white">{{ collection.description_materiaux ||
                                            '-' }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label>Prix</Label>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                                {{ formatNumber(collection.price) }}
                                                <span class="text-lg">{{ collection.devise?.symbole ||
                                                    collection.devise?.code || '' }}</span>
                                            </p>
                                        </div>
                                        <div>
                                            <Label>Devise</Label>
                                            <p class="font-medium text-gray-900 dark:text-white">{{
                                                collection.devise?.libelle || '-' }}</p>
                                        </div>
                                    </div>

                                    <div>
                                        <Label>Statut</Label>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="collection.is_validated
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'">
                                            <CheckCircle v-if="collection.is_validated" class="w-3 h-3 mr-1" />
                                            <Clock v-else class="w-3 h-3 mr-1" />
                                            {{ collection.is_validated ? 'Validée' : 'En attente' }}
                                        </span>
                                    </div>

                                    <div v-if="isAdmin">
                                        <Label>Collecteur</Label>
                                        <p class="text-gray-900 dark:text-white">{{ collection.user?.name || '-' }}</p>
                                        <div v-if="collection.is_validated && collection.validator"
                                            class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Validé par: {{ collection.validator.name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <DeleteDialog v-model:open="showDeleteDialog" :item="collection" resource="collections-prix"
                label="collecte de prix" />
            <ValidateDialog v-model:open="showValidateDialog" :item="collectionToValidate" resource="collections-prix"
                label="collecte de prix" @validated="handleValidated" />
        </AdminLayout>
    </SidebarProvider>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ArrowLeft, Edit, Trash2, Package, MapPin, CheckCircle, Clock } from 'lucide-vue-next'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import DeleteDialog from '@/components/DeleteDialog.vue'
import ValidateDialog from '@/components/ValidateDialog.vue'

interface CollectionPrix {
    id: number
    price: number
    description_materiaux?: string
    quartier_id?: string
    point_vente?: string
    detail?: string
    is_validated: boolean
    commune?: { libelle: string }
    arrondissement?: { libelle: string }
    categorie?: { intitule: string }
    materiau?: { nom: string; unite?: { libelle?: string; nom?: string } }
    devise?: { symbole?: string; code?: string; libelle?: string }
    user?: { name?: string }
    validator?: { name?: string }
    user_id?: number
}

const currentPageTitle = ref('Détails du Prix')
const page = usePage()
const collection = page.props.collection as CollectionPrix
const isAdmin = page.props.isAdmin as boolean
const showDeleteDialog = ref(false)
const showValidateDialog = ref(false)
const collectionToValidate = ref<CollectionPrix | null>(null)

const formatNumber = (value: number) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0)

const canEdit = () => !collection.is_validated && (isAdmin || collection.user_id === page.props.auth?.user?.id)
const canDelete = () => isAdmin && !collection.is_validated
const canValidate = () => isAdmin && !collection.is_validated

const openDeleteDialog = () => showDeleteDialog.value = true
const openValidateDialog = (col: CollectionPrix) => {
    collectionToValidate.value = col
    showValidateDialog.value = true
}

const handleValidated = async () => {
    if (!collectionToValidate.value) return

    // SUPPRIMÉ: la confirmation car elle est déjà faite dans le ValidateDialog
    // if (!confirm('Êtes-vous sûr de vouloir valider cette collecte ?')) return
    
    try {
        await router.post(route('collections-prix.validate', collectionToValidate.value.id), {}, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                // Mettre à jour localement l'état de validation
                collection.is_validated = true
                showValidateDialog.value = false
            },
            onError: (errors) => {
                console.error('Erreur lors de la validation:', errors)
                alert('Une erreur est survenue lors de la validation.')
                showValidateDialog.value = false
            }
        })
    } catch (err) { 
        console.error('Erreur:', err)
        showValidateDialog.value = false
    }
}
</script>