import { h } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { ArrowUpDown, Edit, Trash2 } from 'lucide-vue-next'

export const createColumns = (confirmDelete) => [

    {
        accessorKey: 'nom_bordereau',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Nom du bordereau', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const nom_bordereau = row.getValue('nom_bordereau')
            return h('div', {
                class: 'font-mono text-sm font-bold text-gray-900 dark:text-gray-100'
            }, nom_bordereau)
        },
    },
    {
        accessorKey: 'annee',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Année', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const annee = row.getValue('annee')
            return h('div', {
                class: 'font-mono text-sm font-bold text-gray-900 dark:text-gray-100'
            }, annee)
        },
    },
    {
        accessorKey: 'version',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Version', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const version = row.getValue('version')
            return h('div', {
                class: 'flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 text-blue-700 dark:text-blue-400 font-bold'
            }, version)
        },
    },
    {
        accessorKey: 'actif',
        header: ({ column }) => h(Button, {
            variant: 'ghost',
            onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
        }, () => ['Statut', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })]),
        cell: ({ row }) => {
            const actif = row.getValue('actif')
            const label = actif ? 'Actif' : 'Inactif'
            const colorClass = actif
                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'

            return h('span', {
                class: `px-2 py-1 rounded-full text-xs font-semibold ${colorClass}`
            }, label)
        },
    },

    {
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Actions'),
        cell: ({ row }) => {
            const bordereau = row.original

            return h('div', { class: 'flex justify-end gap-2' }, [
                h(Link, {
                    href: route('bordereaux.show', bordereau.id),
                    class: 'inline-flex items-center px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-md transition-colors'
                }, () => [
                    h({ class: 'w-4 h-4 mr-1' }),
                    'Voir'
                ]),
                h(Link, {
                    href: route('bordereaux.edit', bordereau.id),
                    class: 'inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors'
                }, () => [
                    h(Edit, { class: 'w-4 h-4 mr-1' }),
                    'Modifier'
                ]),
                h(Button, {
                    variant: 'ghost',
                    size: 'sm',
                    onClick: () => confirmDelete(bordereau),
                    class: 'text-red-600 hover:text-red-800 hover:bg-red-50 dark:hover:bg-red-900/20'
                }, () => [
                    h(Trash2, { class: 'w-4 h-4 mr-1' }),
                    'Supprimer'
                ])
            ])
        },
    }

]
