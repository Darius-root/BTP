<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import {
    Plus, Edit, Trash2, Filter, X,
    Package, Ruler, MapPin, DollarSign,
    Eye, CheckCircle, Clock, User
} from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import DeleteDialog from '@/components/DeleteDialog.vue'
import ValidateDialog from '@/components/ValidateDialog.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'

interface Commune { id: number; libelle: string }
interface Categorie { id: number; intitule: string }
interface Collection {
    id: number
    materiau: { nom: string; unite: { libelle: string } }
    description_materiaux: string
    price: number
    devise: { symbole?: string; code: string }
    commune: { libelle: string }
    arrondissement?: { libelle: string }
    quartier_id?: string
    point_vente?: string
    categorie: { intitule: string }
    is_validated: boolean
    user: { id: number; name: string }
    validator?: { name: string }
    can_edit?: boolean
    can_delete?: boolean
    can_validate?: boolean
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface PaginatedData {
    data: Collection[]
    links: PaginationLink[]
    current_page: number
    last_page: number
    per_page: number
    total: number
}

interface Permissions {
    canView: boolean
    canCreate: boolean
    canEdit: boolean
    canDelete: boolean
    canValidate: boolean
}

const currentPageTitle = ref("Collections Prix")

const props = defineProps<{
    collections: PaginatedData
    communes: Commune[]
    categories: Categorie[]
    filters?: { commune_id?: string | number; categorie_id?: string | number }
    permissions: Permissions
    showCollectorColumn?: boolean
}>()

// Filtres
const filters = ref({
    commune_id: props.filters?.commune_id || '',
    categorie_id: props.filters?.categorie_id || ''
})

// Dialogs
const showDeleteDialog = ref(false)
const itemToDelete = ref<Collection | null>(null)
const showValidateDialog = ref(false)
const collectionToValidate = ref<Collection | null>(null)

const confirmDelete = (collection: Collection) => {
    itemToDelete.value = collection
    showDeleteDialog.value = true
}

const openValidateDialog = (collection: Collection) => {
    collectionToValidate.value = collection
    showValidateDialog.value = true
}

const validateCollection = () => {
    if (!collectionToValidate.value) return
    router.post(route('collections-prix.validate', collectionToValidate.value.id), {}, {
        onSuccess: () => {
            if (collectionToValidate.value) {
                collectionToValidate.value.is_validated = true
            }
        }
    })
}

// Actions basées sur les permissions du contrôleur
const canEdit = (collection: Collection) => {
    return props.permissions.canEdit && !collection.is_validated
}

const canDelete = (collection: Collection) => {
    return props.permissions.canDelete
}

const canValidate = (collection: Collection) => {
    return props.permissions.canValidate && !collection.is_validated
}

// Filtres et reset
const applyFilters = () => {
    const query: any = { ...filters.value }
    router.get(route('collections-prix.index'), query, {
        preserveState: true,
        preserveScroll: true
    })
}

const resetFilters = () => {
    filters.value = { commune_id: '', categorie_id: '' }
    router.get(route('collections-prix.index'), {}, {
        preserveState: true,
        preserveScroll: true
    })
}

// Formatage
const formatNumber = (value: number) =>
    new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0)

// Couleurs catégorie
const getCategoryColor = (category: string) => {
    const normalized = category.toUpperCase()
    const colors: Record<string, string> = {
        'GROS-ŒUVRE': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'SECOND-ŒUVRE': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'ÉQUIPEMENT': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        'FINITIONS': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'INSTALLATIONS': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    }
    return colors[normalized] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

// Computed pour les liens de pagination
const prevLink = computed(() => props.collections.links?.[0])
const nextLink = computed(() => props.collections.links?.[props.collections.links.length - 1])

// Navigation de pagination avec filtres
const navigateToPage = (url: string | null) => {
    if (!url) return

    router.get(url, {
        commune_id: filters.value.commune_id || undefined,
        categorie_id: filters.value.categorie_id || undefined
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

// Calcul du nombre de colonnes pour le tableau vide
const tableColspan = computed(() => {
    return props.showCollectorColumn ? 7 : 6
})
</script>

<template>
    <Head title="Collections Prix" />
    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">

                        <!-- Header -->
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Collections Prix</CardTitle>
                                    <CardDescription class="mt-1">
                                        Gérez les prix des matériaux par localisation
                                    </CardDescription>
                                </div>
                                <Link
                                    v-if="permissions.canCreate"
                                    :href="route('collections-prix.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors duration-200">
                                    <Plus class="w-5 h-5 mr-2" /> Nouvelle collecte
                                </Link>
                            </div>
                        </CardHeader>

                        <!-- Filters -->
                        <CardContent class="px-0">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <Label for="commune_id">Commune</Label>
                                    <select
                                        id="commune_id"
                                        v-model="filters.commune_id"
                                        class="w-full mt-1 px-3 py-2 border rounded-md dark:bg-gray-800 dark:border-gray-700">
                                        <option value="">Toutes les communes</option>
                                        <option v-for="commune in communes" :key="commune.id" :value="commune.id">
                                            {{ commune.libelle }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <Label for="categorie_id">Corps d'état</Label>
                                    <select
                                        id="categorie_id"
                                        v-model="filters.categorie_id"
                                        class="w-full mt-1 px-3 py-2 border rounded-md dark:bg-gray-800 dark:border-gray-700">
                                        <option value="">Tous les corps d'état</option>
                                        <option v-for="categorie in categories" :key="categorie.id" :value="categorie.id">
                                            {{ categorie.intitule }}
                                        </option>
                                    </select>
                                </div>
                                <div class="flex items-end gap-2">
                                    <Button @click="applyFilters" class="flex-1">
                                        <Filter class="w-4 h-4 mr-2" /> Filtrer
                                    </Button>
                                    <Button @click="resetFilters" variant="outline">
                                        <X class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Table -->
                            <div class="overflow-x-auto border rounded-md">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Matériau</TableHead>
                                            <TableHead class="w-32">Prix</TableHead>
                                            <TableHead>Localisation</TableHead>
                                            <TableHead>Corps d'état</TableHead>
                                            <TableHead class="w-32">Statut</TableHead>
                                            <TableHead v-if="showCollectorColumn">Collecteur</TableHead>
                                            <TableHead class="text-right w-40">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>

                                    <TableBody>
                                        <TableRow
                                            v-for="collection in collections.data"
                                            :key="collection.id"
                                            class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <TableCell>
                                                <div class="flex items-start gap-3">
                                                    <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                                        <Package class="w-5 h-5" />
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                                            {{ collection.materiau.nom }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                            {{ collection.description_materiaux }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-1">
                                                            <Ruler class="w-3 h-3" /> {{ collection.materiau.unite.libelle }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </TableCell>

                                            <TableCell>
                                                <div class="space-y-1">
                                                    <div class="font-bold text-lg text-green-600 dark:text-green-400">
                                                        {{ formatNumber(collection.price) }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ collection.devise.symbole || collection.devise.code }}
                                                    </div>
                                                </div>
                                            </TableCell>

                                            <TableCell>
                                                <div class="space-y-1">
                                                    <div class="flex items-center gap-1 text-sm text-gray-900 dark:text-gray-100">
                                                        <MapPin class="w-3 h-3" /> {{ collection.commune.libelle }}
                                                    </div>
                                                    <div v-if="collection.arrondissement" class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ collection.arrondissement.libelle }}
                                                    </div>
                                                    <div v-if="collection.quartier_id" class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ collection.quartier_id }}
                                                    </div>
                                                    <div v-if="collection.point_vente" class="text-xs text-blue-600 dark:text-blue-400">
                                                        📍 {{ collection.point_vente }}
                                                    </div>
                                                </div>
                                            </TableCell>

                                            <TableCell>
                                                <div
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    :class="getCategoryColor(collection.categorie.intitule)">
                                                    {{ collection.categorie.intitule }}
                                                </div>
                                            </TableCell>

                                            <TableCell>
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    :class="collection.is_validated
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                        : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'">
                                                    <CheckCircle v-if="collection.is_validated" class="w-3 h-3 mr-1" />
                                                    <Clock v-else class="w-3 h-3 mr-1" />
                                                    {{ collection.is_validated ? 'Validée' : 'En attente' }}
                                                </span>
                                            </TableCell>

                                            <TableCell v-if="showCollectorColumn">
                                                <div class="text-sm">
                                                    <div class="flex items-center gap-1 text-gray-900 dark:text-gray-100">
                                                        <User class="w-3 h-3" /> {{ collection.user.name }}
                                                    </div>
                                                    <div
                                                        v-if="collection.is_validated && collection.validator"
                                                        class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        Validé par: {{ collection.validator.name }}
                                                    </div>
                                                </div>
                                            </TableCell>

                                            <TableCell class="text-right">
                                                <div class="flex justify-end gap-2">
                                                    <Link
                                                        :href="route('collections-prix.show', collection.id)"
                                                        class="inline-flex items-center px-2 py-1.5 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors"
                                                        title="Voir les détails">
                                                        <Eye class="w-4 h-4" />
                                                    </Link>

                                                    <button
                                                        v-if="canValidate(collection)"
                                                        @click="openValidateDialog(collection)"
                                                        class="inline-flex items-center px-2 py-1.5 text-sm text-green-600 hover:bg-green-50 rounded-md transition-colors"
                                                        title="Valider">
                                                        <CheckCircle class="w-4 h-4" />
                                                    </button>

                                                    <Link
                                                        v-if="canEdit(collection)"
                                                        :href="route('collections-prix.edit', collection.id)"
                                                        class="inline-flex items-center px-2 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-md transition-colors"
                                                        title="Modifier">
                                                        <Edit class="w-4 h-4" />
                                                    </Link>

                                                    <button
                                                        v-if="canDelete(collection)"
                                                        @click="confirmDelete(collection)"
                                                        class="inline-flex items-center px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors"
                                                        title="Supprimer">
                                                        <Trash2 class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </TableCell>
                                        </TableRow>

                                        <TableRow v-if="!collections.data.length">
                                            <TableCell :colspan="tableColspan" class="h-24 text-center">
                                                <div class="text-center py-8 text-gray-500">
                                                    <DollarSign class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                                                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                        Aucune collecte trouvée
                                                    </p>
                                                    <p class="text-sm mt-1 text-gray-600 dark:text-gray-400">
                                                        {{ filters.commune_id || filters.categorie_id
                                                            ? 'Aucun résultat pour vos filtres'
                                                            : 'Commencez par créer votre première collecte' }}
                                                    </p>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>

                            <!-- Pagination -->
                            <div
                                v-if="collections.data.length > 0"
                                class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
                                <!-- Info pagination -->
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    Affichage de
                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ ((collections.current_page - 1) * collections.per_page) + 1 }}
                                    </span>
                                    à
                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ Math.min(collections.current_page * collections.per_page, collections.total) }}
                                    </span>
                                    sur
                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ collections.total }}
                                    </span>
                                    résultats
                                </div>

                                <!-- Boutons pagination -->
                                <div class="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        :disabled="!prevLink?.url || prevLink?.active"
                                        @click="navigateToPage(prevLink?.url)">
                                        Précédent
                                    </Button>

                                    <div class="text-sm text-gray-600 dark:text-gray-400 px-2">
                                        Page {{ collections.current_page }} / {{ collections.last_page }}
                                    </div>

                                    <Button
                                        variant="outline"
                                        size="sm"
                                        :disabled="!nextLink?.url || nextLink?.active"
                                        @click="navigateToPage(nextLink?.url)">
                                        Suivant
                                    </Button>
                                </div>
                            </div>

                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Dialogs -->
            <DeleteDialog
                v-model:open="showDeleteDialog"
                :item="itemToDelete"
                resource="collections-prix"
                label="collecte de prix" />

            <ValidateDialog
                v-model:open="showValidateDialog"
                :item="collectionToValidate"
                resource="collections-prix"
                label="collecte de prix"
                @validated="validateCollection" />

        </AdminLayout>
    </SidebarProvider>
</template>
