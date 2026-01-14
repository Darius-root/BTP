<script setup lang="ts">
import { ref } from 'vue'
import {
    FlexRender,
    getCoreRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    getFilteredRowModel,
    useVueTable,
} from '@tanstack/vue-table'

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

const props = defineProps<{
    data: any[],
    columns: any[],
    modelValue?: string
}>()

const emit = defineEmits(['update:modelValue'])

const sorting = ref([])
const globalFilter = ref(props.modelValue || '')

const table = useVueTable({
    get data() { return props.data ?? [] },
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
        <!-- Filtre global -->
        <div class="flex items-center gap-2">
            <Input v-model="globalFilter" placeholder="Rechercher une commune..." class="max-w-sm" />
        </div>

        <!-- Table -->
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
                                :props="header.getContext()" />
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
                            <TableCell :colspan="props.columns.length" class="h-24 text-center">
                                Aucune commune trouvée.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-end space-x-2">
            <Button variant="outline" size="sm" :disabled="!table.getCanPreviousPage()" @click="table.previousPage()">
                Précédent
            </Button>
            <span class="text-sm text-gray-600">
                {{ table.getState().pagination.pageIndex + 1 }}/{{ table.getPageCount() }}
            </span>
            <Button variant="outline" size="sm" :disabled="!table.getCanNextPage()" @click="table.nextPage()">
                Suivant
            </Button>
        </div>
    </div>
</template>
