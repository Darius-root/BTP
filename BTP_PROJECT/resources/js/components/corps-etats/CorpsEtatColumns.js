
import { h } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { ArrowUpDown, Edit, Trash2, Eye } from 'lucide-vue-next'

export const createColumns = (confirmDelete) => [
    {
        accessorKey: 'ordre',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Ordre', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            const ordre = row.getValue('ordre')
            return h('div', {
                class: 'flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 text-blue-700 dark:text-blue-400 font-bold'
            }, ordre)
        },
    },
    {
        accessorKey: 'code',
        header: 'Code',
        cell: ({ row }) => {
            return h('span', {
                class: 'font-mono text-sm font-bold text-gray-900 dark:text-gray-100'
            }, row.getValue('code'))
        },
    },
    {
        accessorKey: 'intitule',
        header: ({ column }) => {
            return h(Button, {
                variant: 'ghost',
                onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
            }, () => ['Intitulé', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
        },
        cell: ({ row }) => {
            return h('div', {}, [
                h('div', { class: 'font-medium text-gray-900 dark:text-gray-100' }, row.getValue('intitule')),
                h('div', { class: 'text-xs text-gray-500 dark:text-gray-400 mt-0.5' }, 'Catégorie de travaux')
            ])
        },
    },
    {
        accessorKey: 'sous_total',
        header: 'Sous-total',
        cell: ({ row }) => {
            const amount = parseFloat(row.getValue('sous_total'))
            const formatted = new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'XOF',
            }).format(amount)

            return h('div', {}, [
                h('div', { class: 'font-bold text-lg text-green-600 dark:text-green-400' }, formatted),
                h('div', { class: 'text-xs text-gray-500 dark:text-gray-400' }, 'Estimation')
            ])
        },
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Actions'),
        cell: ({ row }) => {
            const corpsEtat = row.original

            return h('div', { class: 'flex justify-end gap-2' }, [

                h(Link, {
                    href: route('corps-etat.show', corpsEtat.id),
                    class: 'inline-flex items-center px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/20 rounded-md transition-colors'
                }, () => [
                    h(Eye, { class: 'w-4 h-4 mr-1' }),
                    'Voir'
                ]),
                h(Link, {
                    href: route('corps-etat.edit', corpsEtat.id),
                    class: 'inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors'
                }, () => [
                    h(Edit, { class: 'w-4 h-4 mr-1' }),
                    'Modifier'
                ]),
                h(Button, {
                    variant: 'ghost',
                    size: 'sm',
                    onClick: () => confirmDelete(corpsEtat),
                    class: 'text-red-600 hover:text-red-800 hover:bg-red-50 dark:hover:bg-red-900/20'
                }, () => [
                    h(Trash2, { class: 'w-4 h-4 mr-1' }),
                    'Supprimer'
                ])
            ])
        },
    },
]
