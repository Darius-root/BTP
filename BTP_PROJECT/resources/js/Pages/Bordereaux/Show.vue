<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { h } from 'vue'
import {
    getCoreRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    getFilteredRowModel,
    useVueTable,
    type ColumnDef,
    type SortingState,
    type ColumnFiltersState,
    type VisibilityState,
    FlexRender,
} from '@tanstack/vue-table'
import { valueUpdater } from '@/lib/utils'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Switch } from '@/components/ui/switch'
import {
    ChevronDown,
    ArrowUpDown,
    Search,
    X,
    ArrowLeft,
    Download,
    FileText,
    Calendar,
    Layers,
    Clock,
    ChevronRight,
    Check,
    X as XIcon
} from 'lucide-vue-next'

interface Designation {
    id: number
    code: string
    designation: string
    caracteristiques: string[] | string
    unite_mesure: string
    bi: string
    bs: string
}

interface Bordereau {
    id: number
    nom_bordereau: string
    annee: string
    version: string
    actif: boolean
    created_at: string
    designations: Designation[]
}

const props = defineProps<{
    bordereau: Bordereau
}>()

const currentPageTitle = ref("Détails du bordereau")

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})
const globalFilter = ref('')
const expandedRows = ref<Set<number>>(new Set())
const isChangingStatus = ref(false)

// Référence réactive pour le statut actif
const localBordereau = ref({
    ...props.bordereau,
    actif: props.bordereau.actif
})

// Fonction pour normaliser les caractéristiques
const normalizeCaracteristiques = (carac: string | string[]): string[] => {
    if (!carac) return []

    if (Array.isArray(carac)) {
        return carac.filter(item => item && item.trim() !== '')
    }

    const str = String(carac).trim()
    if (!str) return []

    const separators = /[〉>]|(?:\r?\n)+/
    const items = str.split(separators)
        .map(item => item.trim())
        .filter(item => {
            return item &&
                item.length > 0 &&
                !/^[^a-zA-Z0-9]*$/.test(item)
        })

    if (items.length > 0) return items
    return [str]
}

// Fonction pour parser les montants formatés
const parseFormattedNumber = (formattedStr: string): number => {
    if (!formattedStr) return 0
    return parseFloat(formattedStr.replace(/\s/g, '').replace(',', '.'))
}

// Toggle l'expansion d'une ligne
const toggleRow = (id: number) => {
    if (expandedRows.value.has(id)) {
        expandedRows.value.delete(id)
    } else {
        expandedRows.value.add(id)
    }
}

// Vérifier si une ligne est expansée
const isRowExpanded = (id: number) => {
    return expandedRows.value.has(id)
}

// Toggle le statut actif du bordereau
const toggleBordereauStatus = async () => {
    if (isChangingStatus.value) return

    try {
        isChangingStatus.value = true

        // Animation immédiate du switch
        const newStatus = !localBordereau.value.actif
        localBordereau.value.actif = newStatus

        // Envoi de la requête au serveur
        await router.put(`/bordereaux/${localBordereau.value.id}/toggle-status`, {}, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                // Mise à jour depuis la réponse du serveur
                localBordereau.value.actif = newStatus
            },
            onError: () => {
                // Revert en cas d'erreur
                localBordereau.value.actif = !newStatus
            },
            onFinish: () => {
                isChangingStatus.value = false
            }
        })

    } catch (error) {
        console.error('Erreur lors du changement de statut:', error)
        isChangingStatus.value = false
    }
}

const columns = computed<ColumnDef<Designation>[]>(() => [
    {
        id: 'expand',
        header: () => h('div', { class: 'w-8' }),
        cell: ({ row }) => {
            const designation = row.original
            return h('button', {
                onClick: () => toggleRow(designation.id),
                class: 'p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors'
            }, [
                h(ChevronRight, {
                    class: [
                        'w-5 h-5 transition-transform',
                        isRowExpanded(designation.id) ? 'transform rotate-90' : ''
                    ]
                })
            ])
        },
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'code',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => [
                'Code',
                h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })
            ])
        },
        cell: ({ row }) => h('div', { class: 'font-mono text-sm font-medium' }, row.getValue('code')),
    },
    {
        accessorKey: 'designation',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => [
                'Désignation',
                h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })
            ])
        },
        cell: ({ row }) => h('div', { class: 'max-w-md font-medium' }, row.getValue('designation')),
    },
    {
        accessorKey: 'caracteristiques',
        header: 'Caractéristiques',
        cell: ({ row }) => {
            const rawCarac = row.original.caracteristiques || row.getValue('caracteristiques')
            const carac = normalizeCaracteristiques(rawCarac)

            if (carac.length === 0) {
                return h('div', { class: 'text-gray-400 text-sm italic' }, 'Aucune')
            }
            return h('div', { class: 'text-sm text-gray-600 dark:text-gray-400' },
                `${carac.length} caractéristique(s)`
            )
        },
    },
    {
        accessorKey: 'unite_mesure',
        header: 'Unité',
        cell: ({ row }) => {
            const unite = row.getValue('unite_mesure') as string
            return h('div', { class: 'text-sm font-medium' }, unite || '-')
        },
    },
    {
        accessorKey: 'bi',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => [
                'BI',
                h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })
            ])
        },
        cell: ({ row }) => {
            const bi = row.getValue('bi') as string
            return h('div', { class: 'text-right font-medium' }, bi || '0,00')
        },
        sortingFn: (rowA, rowB) => {
            const a = parseFormattedNumber(rowA.getValue('bi') as string)
            const b = parseFormattedNumber(rowB.getValue('bi') as string)
            return a - b
        },
    },
    {
        accessorKey: 'bs',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => [
                'BS',
                h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })
            ])
        },
        cell: ({ row }) => {
            const bs = row.getValue('bs') as string
            return h('div', { class: 'text-right font-medium' }, bs || '0,00')
        },
        sortingFn: (rowA, rowB) => {
            const a = parseFormattedNumber(rowA.getValue('bs') as string)
            const b = parseFormattedNumber(rowB.getValue('bs') as string)
            return a - b
        },
    },
])

// Filtrer les données localement
const filteredData = computed(() => {
    if (!globalFilter.value) return localBordereau.value.designations

    const searchTerm = globalFilter.value.toLowerCase()
    return localBordereau.value.designations.filter(designation => {
        const carac = normalizeCaracteristiques(designation.caracteristiques)
        const caracStr = carac.join(' ').toLowerCase()

        return designation.code.toLowerCase().includes(searchTerm) ||
            designation.designation.toLowerCase().includes(searchTerm) ||
            (designation.unite_mesure && designation.unite_mesure.toLowerCase().includes(searchTerm)) ||
            caracStr.includes(searchTerm) ||
            (designation.bi && designation.bi.toLowerCase().includes(searchTerm)) ||
            (designation.bs && designation.bs.toLowerCase().includes(searchTerm))
    })
})

const table = useVueTable({
    get data() { return filteredData.value },
    get columns() { return columns.value },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: updaterOrValue => valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: updaterOrValue => valueUpdater(updaterOrValue, columnVisibility),
    state: {
        get sorting() { return sorting.value },
        get columnFilters() { return columnFilters.value },
        get columnVisibility() { return columnVisibility.value },
        get globalFilter() { return globalFilter.value },
    },
    initialState: {
        pagination: {
            pageSize: 15,
        },
    },
})

const clearSearch = () => {
    globalFilter.value = ''
}

// Calculs des totaux
const totalBI = computed(() => {
    return filteredData.value.reduce((sum, designation) => {
        return sum + parseFormattedNumber(designation.bi)
    }, 0)
})

const totalBS = computed(() => {
    return filteredData.value.reduce((sum, designation) => {
        return sum + parseFormattedNumber(designation.bs)
    }, 0)
})

// Format de nombre
const formatNumber = (value: number) => {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value)
}

// Export des données
const exportData = () => {
    const csvContent = [
        ['Code', 'Désignation', 'Caractéristiques', 'Unité de mesure', 'BI', 'BS'],
        ...filteredData.value.map(designation => {
            const carac = normalizeCaracteristiques(designation.caracteristiques)
            return [
                designation.code,
                `"${designation.designation}"`,
                `"${carac.join('; ')}"`,
                designation.unite_mesure || '',
                designation.bi ? designation.bi.replace(/\s/g, '').replace(',', '.') : '0.00',
                designation.bs ? designation.bs.replace(/\s/g, '').replace(',', '.') : '0.00'
            ]
        })
    ].map(row => row.join(';')).join('\n')

    const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    link.href = URL.createObjectURL(blob)
    link.download = `bordereau_${localBordereau.value.nom_bordereau}_${localBordereau.value.annee}_${localBordereau.value.version}.csv`
    link.click()
}

const deleteBordereau = () => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le bordereau "${localBordereau.value.nom_bordereau}" et toutes ses désignations ?`)) {
        router.delete(`/bordereaux/${localBordereau.value.id}`, {
            onSuccess: () => {
                router.visit('/bordereaux')
            }
        })
    }
}
</script>

<template>
    <Head :title="`${localBordereau.nom_bordereau} - ${localBordereau.annee}`" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <!-- En-tête avec informations du bordereau -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6 shadow-sm">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                        <!-- Informations principales -->
                        <div class="flex-1 space-y-4">
                            <div class="flex items-start gap-4">
                                <Link href="/bordereaux"
                                    class="mt-1 p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                                    <ArrowLeft class="w-5 h-5" />
                                </Link>
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                            {{ localBordereau.nom_bordereau }}
                                        </h1>
                                        <div v-if="localBordereau.actif"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs font-medium">
                                            <Check class="w-3 h-3" />
                                            Actif
                                        </div>
                                        <div v-else
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-medium">
                                            <XIcon class="w-3 h-3" />
                                            Inactif
                                        </div>
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                                        Détails complets du bordereau et de ses désignations
                                    </p>
                                </div>
                            </div>

                            <!-- Statistiques -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div class="flex items-center gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <Calendar class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                                    <div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Année</div>
                                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                            {{ localBordereau.annee }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                    <Layers class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                                    <div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Version</div>
                                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                            {{ localBordereau.version }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                    <FileText class="w-8 h-8 text-green-600 dark:text-green-400" />
                                    <div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Désignations</div>
                                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                            {{ localBordereau.designations.length }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                                    <Clock class="w-8 h-8 text-orange-600 dark:text-orange-400" />
                                    <div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Importé le</div>
                                        <div class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                            {{ localBordereau.created_at }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Statut avec switch -->
                                <div class="flex items-center gap-3 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                                    <div class="w-8 h-8 flex items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-800">
                                        <div v-if="localBordereau.actif"
                                            class="w-4 h-4 rounded-full bg-green-500 transition-colors duration-300"></div>
                                        <div v-else
                                            class="w-4 h-4 rounded-full bg-gray-400 transition-colors duration-300"></div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Statut</div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <Switch
                                                :model-value="localBordereau.actif"
                                                @update:model-value="toggleBordereauStatus"
                                                :disabled="isChangingStatus"
                                                :class="{
                                                    'opacity-50 cursor-not-allowed': isChangingStatus,
                                                    'transition-all duration-300': true
                                                }"
                                            />
                                            <span class="text-sm font-medium transition-colors duration-300"
                                                :class="{
                                                    'text-green-600 dark:text-green-400': localBordereau.actif,
                                                    'text-gray-600 dark:text-gray-400': !localBordereau.actif
                                                }">
                                                {{ localBordereau.actif ? 'Actif' : 'Inactif' }}
                                            </span>
                                            <span v-if="isChangingStatus"
                                                  class="text-xs text-blue-600 dark:text-blue-400 animate-pulse">
                                                Changement...
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-2 lg:min-w-[200px]">
                            <Button @click="exportData" variant="default" class="w-full">
                                <Download class="w-4 h-4 mr-2" />
                                Exporter CSV
                            </Button>
                            <Button @click="deleteBordereau" variant="destructive" class="w-full">
                                Supprimer
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Tableau des désignations -->
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6 shadow-sm">
                    <Card class="border-0 shadow-none">
                        <CardHeader class="px-0 pt-0">
                            <CardTitle class="text-2xl">Désignations du bordereau</CardTitle>
                            <CardDescription class="mt-1">
                                Liste complète des {{ localBordereau.designations.length }} désignations
                            </CardDescription>
                        </CardHeader>

                        <CardContent class="px-0">
                            <div class="space-y-6">
                                <!-- Barre de recherche -->
                                <div class="flex flex-col sm:flex-row gap-4 items-end">
                                    <div class="flex-1">
                                        <Label for="searchFilter">Rechercher dans les désignations</Label>
                                        <div class="relative mt-1">
                                            <Search
                                                class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400 z-10" />
                                            <Input id="searchFilter" type="text"
                                                placeholder="Code, désignation, caractéristiques, unité, BI, BS..."
                                                v-model="globalFilter" class="pl-10 pr-10 w-full" autocomplete="off" />
                                            <button v-if="globalFilter" @click="clearSearch"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 z-10">
                                                <X class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Sélection des colonnes -->
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="outline">
                                                Colonnes
                                                <ChevronDown class="w-4 h-4 ml-2" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuCheckboxItem
                                                v-for="column in table.getAllColumns().filter((column) => column.getCanHide())"
                                                :key="column.id" class="capitalize" :model-value="column.getIsVisible()"
                                                @update:model-value="(value) => column.toggleVisibility(!!value)">
                                                {{
                                                    column.id === 'caracteristiques' ? 'Caractéristiques' :
                                                        column.id === 'unite_mesure' ? 'Unité de mesure' :
                                                            column.id === 'designation' ? 'Désignation' :
                                                                column.id
                                                }}
                                            </DropdownMenuCheckboxItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>

                                <!-- Info résultats -->
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ filteredData.length }} désignation(s) affichée(s)
                                    <template v-if="filteredData.length !== localBordereau.designations.length">
                                        sur {{ localBordereau.designations.length }}
                                    </template>
                                </div>

                                <!-- DataTable -->
                                <div class="overflow-x-auto border rounded-md">
                                    <Table>
                                        <TableHeader>
                                            <TableRow v-for="headerGroup in table.getHeaderGroups()"
                                                :key="headerGroup.id">
                                                <TableHead v-for="header in headerGroup.headers" :key="header.id">
                                                    <template v-if="!header.isPlaceholder">
                                                        <FlexRender :render="header.column.columnDef.header"
                                                            :props="header.getContext()" />
                                                    </template>
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <template v-if="table.getRowModel().rows?.length">
                                                <template v-for="row in table.getRowModel().rows" :key="row.id">
                                                    <!-- Ligne principale -->
                                                    <TableRow class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                                            <FlexRender :render="cell.column.columnDef.cell"
                                                                :props="cell.getContext()" />
                                                        </TableCell>
                                                    </TableRow>

                                                    <!-- Ligne détails (expansible) -->
                                                    <TableRow v-if="isRowExpanded(row.original.id)"
                                                        class="bg-blue-50 dark:bg-blue-900/10 border-t-0">
                                                        <TableCell :colspan="columns.length" class="p-6">
                                                            <div class="space-y-4">
                                                                <!-- En-tête de la section détails -->
                                                                <div
                                                                    class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                                    <FileText class="w-4 h-4" />
                                                                    <span>Détails de la désignation</span>
                                                                </div>

                                                                <!-- Grille d'informations -->
                                                                <div
                                                                    class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                                                    <!-- Code -->
                                                                    <div>
                                                                        <div
                                                                            class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">
                                                                            Code
                                                                        </div>
                                                                        <div
                                                                            class="font-mono font-semibold text-gray-900 dark:text-gray-100">
                                                                            {{ row.original.code }}
                                                                        </div>
                                                                    </div>

                                                                    <!-- Unité de mesure -->
                                                                    <div>
                                                                        <div
                                                                            class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">
                                                                            Unité de mesure
                                                                        </div>
                                                                        <div
                                                                            class="font-semibold text-gray-900 dark:text-gray-100">
                                                                            {{ row.original.unite_mesure || '-' }}
                                                                        </div>
                                                                    </div>

                                                                    <!-- BI -->
                                                                    <div>
                                                                        <div
                                                                            class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">
                                                                            Bordereau d'inventaire (BI)
                                                                        </div>
                                                                        <div
                                                                            class="font-semibold text-lg text-blue-600 dark:text-blue-400">
                                                                            {{ row.original.bi || '0,00' }}
                                                                        </div>
                                                                    </div>

                                                                    <!-- BS -->
                                                                    <div>
                                                                        <div
                                                                            class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">
                                                                            Bordereau de situation (BS)
                                                                        </div>
                                                                        <div
                                                                            class="font-semibold text-lg text-green-600 dark:text-green-400">
                                                                            {{ row.original.bs || '0,00' }}
                                                                        </div>
                                                                    </div>

                                                                    <!-- Désignation complète -->
                                                                    <div class="md:col-span-2">
                                                                        <div
                                                                            class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">
                                                                            Désignation complète
                                                                        </div>
                                                                        <div
                                                                            class="text-gray-900 dark:text-gray-100 leading-relaxed">
                                                                            {{ row.original.designation }}
                                                                        </div>
                                                                    </div>

                                                                    <!-- Caractéristiques -->
                                                                    <div class="md:col-span-2">
                                                                        <div
                                                                            class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">
                                                                            Caractéristiques ({{
                                                                            normalizeCaracteristiques(row.original.caracteristiques).length
                                                                            }})
                                                                        </div>

                                                                        <div v-if="normalizeCaracteristiques(row.original.caracteristiques).length > 0"
                                                                            class="space-y-1">
                                                                            <div v-for="(carac, index) in normalizeCaracteristiques(row.original.caracteristiques)"
                                                                                :key="index"
                                                                                class="flex items-start gap-2 py-1">
                                                                                <div
                                                                                    class="flex-shrink-0 w-5 h-5 flex items-center justify-center text-xs font-medium text-blue-600 dark:text-blue-400">
                                                                                    {{ index + 1 }}.
                                                                                </div>
                                                                                <div class="flex-1">
                                                                                    <div
                                                                                        class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
                                                                                        {{ carac }}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div v-else
                                                                            class="text-gray-400 dark:text-gray-500 italic text-sm">
                                                                            Aucune caractéristique spécifiée
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </TableCell>
                                                    </TableRow>
                                                </template>
                                            </template>
                                            <template v-else>
                                                <TableRow>
                                                    <TableCell :colspan="columns.length" class="h-24 text-center">
                                                        Aucune désignation trouvée
                                                    </TableCell>
                                                </TableRow>
                                            </template>
                                        </TableBody>
                                    </Table>
                                </div>

                                <!-- Pagination -->
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-700 dark:text-gray-300">
                                        Page {{ table.getState().pagination.pageIndex + 1 }} sur {{ table.getPageCount()
                                        }}
                                    </div>
                                    <div class="flex gap-2">
                                        <Button variant="outline" size="sm" @click="() => table.previousPage()"
                                            :disabled="!table.getCanPreviousPage()">
                                            Précédent
                                        </Button>
                                        <Button variant="outline" size="sm" @click="() => table.nextPage()"
                                            :disabled="!table.getCanNextPage()">
                                            Suivant
                                        </Button>
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
