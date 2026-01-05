<template>
    <Head title="Collections Prix" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <!-- Card principale -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <CardTitle class="text-3xl">Collections Prix</CardTitle>
                                    <CardDescription class="mt-1">
                                        Gérez les prix des matériaux par localisation
                                    </CardDescription>
                                </div>
                                <Link
                                    :href="route('collections-prix.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition-colors duration-200"
                                >
                                    <Plus class="w-5 h-5 mr-2" />
                                    Nouveau prix
                                </Link>
                            </div>
                        </CardHeader>

                        <CardContent class="px-0">
                            <div class="space-y-6">
                                <!-- Barre de filtres -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Filtre par commune -->
                                    <div>
                                        <Label for="commune_id">Commune</Label>
                                        <select
                                            id="commune_id"
                                            v-model="filters.commune_id"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                        >
                                            <option value="">Toutes les communes</option>
                                            <option
                                                v-for="commune in communes"
                                                :key="commune.id"
                                                :value="commune.id"
                                            >
                                                {{ commune.libelle }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Filtre par catégorie -->
                                    <div>
                                        <Label for="categorie_id">Catégorie</Label>
                                        <select
                                            id="categorie_id"
                                            v-model="filters.categorie_id"
                                            class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                        >
                                            <option value="">Toutes les catégories</option>
                                            <option
                                                v-for="categorie in categories"
                                                :key="categorie.id"
                                                :value="categorie.id"
                                            >
                                                {{ categorie.intitule }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Boutons actions filtres -->
                                    <div class="flex items-end gap-2">
                                        <Button @click="applyFilters" class="flex-1">
                                            <Filter class="w-4 h-4 mr-2" />
                                            Filtrer
                                        </Button>
                                        <Button @click="resetFilters" variant="outline">
                                            <X class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </div>

                                <!-- Info résultats -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        Affichage de {{ collections.data.length }} prix
                                        <span v-if="collections.total !== collections.data.length">
                                            sur {{ collections.total }} au total
                                        </span>
                                    </div>

                                    <!-- Statistiques -->
                                    <div class="flex items-center gap-4">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            Page {{ collections.current_page }} sur {{ collections.last_page }}
                                        </div>
                                    </div>
                                </div>

                                <!-- DataTable -->
                                <div class="overflow-x-auto border rounded-md">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Matériau</TableHead>
                                                <TableHead class="w-32">Prix</TableHead>
                                                <TableHead>Localisation</TableHead>
                                                <TableHead>Catégorie</TableHead>
                                                <TableHead class="w-24">Statut</TableHead>
                                                <TableHead class="text-right w-32">Actions</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <template v-if="collections.data.length">
                                                <TableRow
                                                    v-for="collection in collections.data"
                                                    :key="collection.id"
                                                    class="hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                                >
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
                                                                    <Ruler class="w-3 h-3" />
                                                                    {{ collection.materiau.unite.libelle }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div class="flex items-center gap-2">
                                                            <div class="font-bold text-lg text-green-600 dark:text-green-400">
                                                                {{ formatNumber(collection.price) }}
                                                            </div>
                                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                {{ collection.devise.symbole || collection.devise.code }}
                                                            </div>
                                                        </div>
                                                        <div v-if="collection.point_vente" class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[120px]">
                                                            📍 {{ collection.point_vente }}
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div class="space-y-1">
                                                            <div class="flex items-center gap-1 text-sm text-gray-900 dark:text-gray-100">
                                                                <MapPin class="w-3 h-3" />
                                                                {{ collection.commune.libelle }}
                                                            </div>
                                                            <div v-if="collection.arrondissement" class="text-xs text-gray-500 dark:text-gray-400">
                                                                {{ collection.arrondissement.libelle }}
                                                            </div>
                                                            <div v-if="collection.quartier_id" class="text-xs text-gray-500 dark:text-gray-400">
                                                                Quartier: {{ collection.quartier_id }}
                                                            </div>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                            :class="getCategoryColor(collection.categorie.intitule)">
                                                            {{ collection.categorie.intitule }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                            {{ collection.categorie.code }}
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                            :class="collection.status ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'"
                                                        >
                                                            {{ collection.status ? 'Actif' : 'Inactif' }}
                                                        </span>
                                                    </TableCell>
                                                    <TableCell class="text-right">
                                                        <div class="flex justify-end gap-2">
                                                            <Link
                                                                :href="route('collections-prix.edit', collection.id)"
                                                                class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors"
                                                                title="Modifier"
                                                            >
                                                                <Edit class="w-4 h-4" />
                                                            </Link>
                                                            <button
                                                                @click="confirmDelete(collection)"
                                                                class="inline-flex items-center p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                                                title="Supprimer"
                                                            >
                                                                <Trash2 class="w-4 h-4" />
                                                            </button>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            </template>
                                            <template v-else>
                                                <TableRow>
                                                    <TableCell colspan="6" class="h-24 text-center">
                                                        <div class="text-center py-8 text-gray-500">
                                                            <DollarSign class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                                                            <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                                Aucun prix trouvé
                                                            </p>
                                                            <p class="text-sm mt-1 text-gray-600 dark:text-gray-400">
                                                                {{ filters.commune_id || filters.categorie_id ? 'Aucun résultat pour vos filtres' : 'Commencez par créer votre premier prix' }}
                                                            </p>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            </template>
                                        </TableBody>
                                    </Table>
                                </div>

                                <!-- Pagination -->
                                <div v-if="collections.data.length > 0" class="flex items-center justify-between">
                                    <div class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ collections.from }}-{{ collections.to }} sur {{ collections.total }}
                                    </div>
                                    <div class="flex gap-2">
                                        <Link
                                            v-if="collections.prev_page_url"
                                            :href="collections.prev_page_url"
                                            preserve-scroll
                                            class="inline-flex items-center px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                        >
                                            <ChevronLeft class="w-4 h-4 mr-1" />
                                            Précédent
                                        </Link>
                                        <Link
                                            v-if="collections.next_page_url"
                                            :href="collections.next_page_url"
                                            preserve-scroll
                                            class="inline-flex items-center px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                        >
                                            Suivant
                                            <ChevronRight class="w-4 h-4 ml-1" />
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import {
    Plus, Edit, Trash2, Filter, X,
    Package, Ruler, MapPin, DollarSign,
    ChevronLeft, ChevronRight
} from 'lucide-vue-next'
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

const currentPageTitle = ref("Collections Prix")

const props = defineProps({
    collections: {
        type: Object,
        required: true,
    },
    communes: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const filters = ref({
    commune_id: props.filters.commune_id || '',
    categorie_id: props.filters.categorie_id || ''
})

const applyFilters = () => {
    const query = {}
    if (filters.value.commune_id) query.commune_id = filters.value.commune_id
    if (filters.value.categorie_id) query.categorie_id = filters.value.categorie_id

    router.get(route('collections-prix.index'), query, {
        preserveState: true,
        preserveScroll: true,
    })
}

const resetFilters = () => {
    filters.value = { commune_id: '', categorie_id: '' }
    router.get(route('collections-prix.index'), {}, {
        preserveState: true,
        preserveScroll: true,
    })
}

const confirmDelete = (collection) => {
    const materialInfo = `${collection.materiau.nom} (${collection.materiau.unite.libelle})`
    const locationInfo = collection.commune.libelle + (collection.arrondissement ? ` - ${collection.arrondissement.libelle}` : '')

    if (confirm(`Êtes-vous sûr de vouloir supprimer le prix pour "${materialInfo}" à "${locationInfo}" ? Cette action est irréversible.`)) {
        router.delete(route('collections-prix.destroy', collection.id))
    }
}

const formatNumber = (value) => {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0)
}

const getCategoryColor = (category) => {
    const colors = {
        'GROS-ŒUVRE': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'SECOND-ŒUVRE': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'ÉQUIPEMENT': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        'FINITIONS': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'INSTALLATIONS': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    }

    return colors[category] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

// Réappliquer les filtres quand ils changent
watch(filters, () => {
    // On pourrait ajouter un délai pour éviter les requêtes trop fréquentes
    // debouncedApplyFilters()
}, { deep: true })
</script>
