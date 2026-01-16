<script setup>
import { ref } from 'vue'
import {
    FlexRender,
    getCoreRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    getFilteredRowModel,
    useVueTable,
} from '@tanstack/vue-table'
import { Search, FileText } from 'lucide-vue-next'

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

const props = defineProps({
    data: {
        type: Array,
        required: true
    },
    columns: {
        type: Array,
        required: true
    },
    modelValue: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['update:modelValue'])

const sorting = ref([])
const globalFilter = ref(props.modelValue || '')

const table = useVueTable({
    get data() { return props.data },
    get columns() { return props.columns },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onSortingChange: updaterOrValue => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue
    },
    onGlobalFilterChange: updaterOrValue => {
        globalFilter.value = typeof updaterOrValue === 'function' ? updaterOrValue(globalFilter.value) : updaterOrValue
        emit('update:modelValue', globalFilter.value)
    },
    state: {
        get sorting() { return sorting.value },
        get globalFilter() { return globalFilter.value },
    },
})
</script>

<template>
    <div class="space-y-4">
        <!-- Barre de recherche -->
        <div class="flex items-center gap-4">
            <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
                <Input
                    v-model="globalFilter"
                    placeholder="Rechercher un template..."
                    class="pl-9 h-11"
                />
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-lg border border-gray-200 dark:border-gray-800">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <FlexRender 
                                v-if="!header.isPlaceholder" 
                                :render="header.column.columnDef.header"
                                :props="header.getContext()" 
                            />
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-if="table.getRowModel().rows.length">
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="props.columns.length" class="h-24">
                                <div class="flex flex-col items-center justify-center py-12 text-center">
                                    <FileText class="h-12 w-12 text-gray-400 mb-4" />
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        Aucun template trouvé
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        Il n'y a pas encore de templates de devis disponibles.
                                    </p>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-end space-x-2">
            <Button 
                variant="outline" 
                size="sm" 
                :disabled="!table.getCanPreviousPage()" 
                @click="table.previousPage()"
            >
                Précédent
            </Button>
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ table.getState().pagination.pageIndex + 1 }}/{{ table.getPageCount() }}
            </span>
            <Button 
                variant="outline" 
                size="sm" 
                :disabled="!table.getCanNextPage()" 
                @click="table.nextPage()"
            >
                Suivant
            </Button>
        </div>
    </div>
</template>