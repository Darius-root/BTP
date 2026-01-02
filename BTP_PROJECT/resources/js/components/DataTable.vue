<script setup lang="ts" generic="TData, TValue">
import type {
  ColumnDef,
  SortingState,
  VisibilityState,
} from '@tanstack/vue-table'
import {
  FlexRender,
  getCoreRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
} from '@tanstack/vue-table'
import { ref, watch, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { valueUpdater } from '@/components/ui/table/utils'

/* PROPS */
const props = defineProps<{
  columns: ColumnDef<TData, TValue>[]
  data: TData[]
  meta: {
    current_page: number
    last_page: number
    total: number
  }
}>()

/* EVENTS */
const emit = defineEmits<{
  (
    e: 'search',
    payload: {
      value: string
      columns: string[]
    }
  ): void
  (e: 'page-change', page: number): void
}>()

/* STATE */
const sorting = ref<SortingState>([])
const columnVisibility = ref<VisibilityState>({})
const search = ref('')

/* TABLE */
const table = useVueTable({
  get data() {
    return props.data
  },
  get columns() {
    return props.columns
  },
  getCoreRowModel: getCoreRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  onSortingChange: updater =>
    valueUpdater(updater, sorting),
  onColumnVisibilityChange: updater =>
    valueUpdater(updater, columnVisibility),
  state: {
    get sorting() {
      return sorting.value
    },
    get columnVisibility() {
      return columnVisibility.value
    },
  },
})

/* COLONNES VISIBLES (clé du système) */
const visibleColumns = computed(() =>
  table
    .getAllLeafColumns()
    .filter(col => col.getIsVisible() && col.getCanHide())
    .map(col => col.id)
)

/* SEARCH → BACKEND */
watch(
  () => search.value,
  value => {
    emit('search', {
      value,
      columns: visibleColumns.value,
    })
  }
)
</script>

<template>
  <div class="w-full space-y-4">
    <!-- Toolbar -->
    <div class="flex items-center gap-2">
      <Input
        v-model="search"
        class="max-w-sm"
        placeholder="Rechercher..."
      />

      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <Button variant="outline" class="ml-auto">
            Colonnes
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
          <DropdownMenuCheckboxItem
           v-for="column in table.getAllColumns().filter(c => c.getCanHide() && c.id !== 'id')"
    :key="column.id"
    :model-value="column.getIsVisible()"
    @update:model-value="v => column.toggleVisibility(!!v)"
  >
    {{ column.id }}
          </DropdownMenuCheckboxItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>

    <!-- Table -->
    <div class="rounded-md border">
      <Table>
        <TableHeader class="pl-4 ">
          <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id">
            <TableHead v-for="header in hg.headers" :key="header.id">
              <FlexRender
                v-if="!header.isPlaceholder"
                :render="header.column.columnDef.header"
                :props="header.getContext()"
              />
            </TableHead>
          </TableRow><DropdownMenuContent align="end">
  <DropdownMenuCheckboxItem
    v-for="column in table.getAllColumns().filter(c => c.getCanHide() && c.id !== 'id')"
    :key="column.id"
    :model-value="column.getIsVisible()"
    @update:model-value="v => column.toggleVisibility(!!v)"
  >
    {{ column.id }}
  </DropdownMenuCheckboxItem>
</DropdownMenuContent>
        </TableHeader>

        <TableBody>
          <TableRow
            v-for="row in table.getRowModel().rows"
            :key="row.id"
          >
            <TableCell
              v-for="cell in row.getVisibleCells()"
              :key="cell.id"
            >
              <FlexRender
                :render="cell.column.columnDef.cell"
                :props="cell.getContext()"
              />
            </TableCell>
          </TableRow>

          <TableRow v-if="!table.getRowModel().rows.length">
            <TableCell
              :colspan="columns.length"
              class="text-center py-8"
            >
              Aucun résultat
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-end gap-2">
      <Button
        variant="outline"
        size="sm"
        :disabled="meta.current_page === 1"
        @click="emit('page-change', meta.current_page - 1)"
      >
        Précédent
      </Button>

      <span class="text-sm text-muted-foreground">
        Page {{ meta.current_page }} / {{ meta.last_page }}
      </span>

      <Button
        variant="outline"
        size="sm"
        :disabled="meta.current_page === meta.last_page"
        @click="emit('page-change', meta.current_page + 1)"
      >
        Suivant
      </Button>
    </div>
  </div>
</template>
