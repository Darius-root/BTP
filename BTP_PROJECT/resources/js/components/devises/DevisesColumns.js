import { h } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { ArrowUpDown, Edit, Trash2 , Eye } from 'lucide-vue-next'

export const createDeviseColumns = (confirmDelete) => [
    {
        accessorKey: 'code',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Code', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            return h('span', {
                class: 'font-mono text-sm font-bold text-gray-900 dark:text-gray-100'
            }, row.getValue('code'))
        },
    },
    {
        accessorKey: 'libelle',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Libellé', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            return h('div', { class: 'font-medium text-gray-900 dark:text-gray-100' }, row.getValue('libelle'))
        },
    },
    {
        accessorKey: 'symbole',
        header: 'Symbole',
        cell: ({ row }) => {
            return h('span', {
                class: 'text-sm text-gray-700 dark:text-gray-300'
            }, row.getValue('symbole') || '-')
        },
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Actions'),
        cell: ({ row }) => {
            const devise = row.original

            return h('div', { class: 'flex justify-end gap-2' }, [
                 h(Link, {
                    href: route('devises.show', devise.id),
                    class: 'inline-flex items-center px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/20 rounded-md transition-colors'
                }, () => [
                    h(Eye, { class: 'w-4 h-4 mr-1' }),
                    'Voir'
                ]),
                h(Link, {
                    href: route('devises.edit', devise.id),
                    class: 'inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors'
                }, () => [
                    h(Edit, { class: 'w-4 h-4 mr-1' }),
                    'Modifier'
                ]),
                h(Button, {
                    variant: 'ghost',
                    size: 'sm',
                    onClick: () => confirmDelete(devise),
                    class: 'text-red-600 hover:text-red-800 hover:bg-red-50 dark:hover:bg-red-900/20'
                }, () => [
                    h(Trash2, { class: 'w-4 h-4 mr-1' }),
                    'Supprimer'
                ])
            ])
        },
    },
]
