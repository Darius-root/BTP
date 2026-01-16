import { h } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { ArrowUpDown, Edit, Trash2, Eye } from 'lucide-vue-next'

export const createCommunesColumns = (onDelete) => [
    {
        accessorKey: 'code',
        header: ({ column }) =>
            h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Code', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })]),
        cell: ({ row }) =>
            h('span', { class: 'font-mono text-sm font-bold text-gray-900 dark:text-gray-100' }, row.getValue('code')),
    },
    {
        accessorKey: 'libelle',
        header: ({ column }) =>
            h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Libellé', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })]),
        cell: ({ row }) =>
            h('div', {}, [
                h('div', { class: 'font-medium text-gray-900 dark:text-gray-100' }, row.getValue('libelle')),
                h('div', { class: 'text-xs text-gray-500 dark:text-gray-400 mt-0.5' }, 'Commune')
            ]),
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) =>
            h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Date de création', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })]),
        cell: ({ row }) => {
            const date = new Date(row.getValue('created_at'))
            return h('div', {}, [
                h('div', { class: 'text-sm font-medium text-gray-900 dark:text-gray-100' }, date.toLocaleDateString('fr-FR')),
                h('div', { class: 'text-xs text-gray-500 dark:text-gray-400' }, date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }))
            ])
        },
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Actions'),
        cell: ({ row }) => {
            const commune = row.original

            return h('div', { class: 'flex justify-end gap-2' }, [
                // Voir
                h(Link, {
                    href: route('communes.show', { commune: commune.id }),
                    class: 'inline-flex items-center px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/20 rounded-md transition-colors'
                }, () => [h(Eye, { class: 'w-4 h-4 mr-1' }), 'Voir']),

                // Modifier
                h(Link, {
                    href: route('communes.edit', { commune: commune.id }),
                    class: 'inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors'
                }, () => [h(Edit, { class: 'w-4 h-4 mr-1' }), 'Modifier']),

                // Supprimer
                h(Button, {
                    variant: 'ghost',
                    size: 'sm',
                    onClick: () => onDelete(commune),
                    class: 'text-red-600 hover:text-red-800 hover:bg-red-50 dark:hover:bg-red-900/20'
                }, () => [
                    h(Trash2, { class: 'w-4 h-4 mr-1' }),
                    'Supprimer'
                ])
            ])
        },
    },
]
