// resources/js/Pages/NiveauxBatiment/components/NiveauxBatimentColumns.js

import { h } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { ArrowUpDown, Edit, Trash2 , Eye } from 'lucide-vue-next'

export const createColumns = (confirmDelete) => [
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
        accessorKey: 'nom',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Nom', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            return h('div', {}, [
                h('div', { class: 'font-medium text-gray-900 dark:text-gray-100' }, row.getValue('nom')),
                h('div', { class: 'text-xs text-gray-500 dark:text-gray-400 mt-0.5' }, 'Niveau de bâtiment')
            ])
        },
    },
   

    {
        accessorKey: 'created_at',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Date de création', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const date = new Date(row.getValue('created_at'))
            return h('div', {}, [
                h('div', { class: 'text-sm font-medium text-gray-900 dark:text-gray-100' },
                    date.toLocaleDateString('fr-FR')),
                h('div', { class: 'text-xs text-gray-500 dark:text-gray-400' },
                    date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }))
            ])
        },
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Actions'),
        cell: ({ row }) => {
            const niveau = row.original

            return h('div', { class: 'flex justify-end gap-2' }, [
                h(Link, {
                    href: route('niveaux-batiment.show', niveau.id),
                    class: 'inline-flex items-center px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/20 rounded-md transition-colors'
                }, () => [
                    h(Eye, { class: 'w-4 h-4 mr-1' }),
                    'Voir'
                ]),

                h(Link, {
                    href: route('niveaux-batiment.edit', niveau.id),
                    class: 'inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors'
                }, () => [
                    h(Edit, { class: 'w-4 h-4 mr-1' }),
                    'Modifier'
                ]),
                h(Button, {
                    variant: 'ghost',
                    size: 'sm',
                    onClick: () => confirmDelete(niveau),
                    class: 'text-red-600 hover:text-red-800 hover:bg-red-50 dark:hover:bg-red-900/20'
                }, () => [
                    h(Trash2, { class: 'w-4 h-4 mr-1' }),
                    'Supprimer'
                ])
            ])
        },
    },
]
