import { h } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { ArrowUpDown, Edit, Trash2, Eye } from 'lucide-vue-next'

export const createUnitesColumns = (onDelete) => [
    {
        accessorKey: 'code',
        header: ({ column }) =>
            h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Code', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })]),
        cell: ({ row }) =>
            h('span', { class: 'font-mono text-sm font-bold text-blue-600 dark:text-blue-400' }, row.getValue('code')),
    },
    {
        accessorKey: 'libelle',
        header: ({ column }) =>
            h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Libellé', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })]),
        cell: ({ row }) =>
            h('div', { class: 'font-medium text-gray-900 dark:text-gray-100' }, row.getValue('libelle')),
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Actions'),
        cell: ({ row }) => {
            const unite = row.original

            return h('div', { class: 'flex justify-end gap-2' }, [
                // Voir
                // Voir
                h(Link, {
                    href: route('unites-mesure.show', { uniteMesure: unite.id }),
                    class: 'inline-flex items-center px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-md transition-colors'
                }, () => [
                    h(Eye, { class: 'w-4 h-4 mr-1' }),
                    'Voir'
                ]),

                // Modifier
                h(Link, {
                    href: route('unites-mesure.edit', { uniteMesure: unite.id }),
                    class: 'inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors'
                }, () => [
                    h(Edit, { class: 'w-4 h-4 mr-1' }),
                    'Modifier'
                ]),


                // Supprimer
                h(Button, {
                    variant: 'ghost',
                    size: 'sm',
                    onClick: () => onDelete(unite),
                    class: 'text-red-600 hover:text-red-800 hover:bg-red-50'
                }, () => [
                    h(Trash2, { class: 'w-4 h-4 mr-1' }),
                    'Supprimer'
                ])
            ])
        },
    },
]