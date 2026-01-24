import { h } from 'vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { MoreHorizontal, Eye, Pencil, Trash2, Shield } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

export function createTemplatesColumns(onViewDetails, onDelete, currentUser) {
    return [
        {
            accessorKey: 'code',
            header: 'Code',
            cell: ({ row }) => {
                const template = row.original
                return h('div', { class: 'flex items-center gap-2' }, [
                    h('span', { class: 'font-mono font-semibold text-blue-600 dark:text-blue-400' }, template.code),
                    template.organisation.is_system
                        ? h(Shield, { class: 'w-4 h-4 text-green-600 dark:text-green-400' })
                        : null
                ])
            },
        },
        {
            accessorKey: 'intitule',
            header: 'Intitulé',
            cell: ({ row }) => {
                const template = row.original
                return h('div', { class: 'max-w-md' }, [
                    h('p', { class: 'font-medium text-gray-900 dark:text-gray-100 truncate' }, template.intitule),
                    h('p', { class: 'text-sm text-gray-500 dark:text-gray-400 truncate' },
                        `${template.projet.nom} / ${template.batiment.nom}`
                    )
                ])
            },
        },
       
        {
            accessorKey: 'created_at',
            header: 'Date de création',
            cell: ({ row }) => {
                const date = row.getValue('created_at')
                return h('span', { class: 'text-sm text-gray-600 dark:text-gray-400' }, date)
            },
        },
        {
            id: 'actions',
            header: 'Actions',
            cell: ({ row }) => {
                const template = row.original
                
                // Vérifier si l'utilisateur peut modifier/supprimer
                const canEdit = currentUser && (
                    currentUser.id === template.created_by || 
                    currentUser.is_admin || 
                    currentUser.role === 'admin'
                )

                return h(
                    DropdownMenu,
                    {},
                    {
                        default: () => [
                            h(
                                DropdownMenuTrigger,
                                { asChild: true },
                                {
                                    default: () =>
                                        h(
                                            Button,
                                            {
                                                variant: 'ghost',
                                                class: 'h-8 w-8 p-0',
                                            },
                                            {
                                                default: () => [
                                                    h('span', { class: 'sr-only' }, 'Ouvrir le menu'),
                                                    h(MoreHorizontal, { class: 'h-4 w-4' }),
                                                ],
                                            }
                                        ),
                                }
                            ),
                            h(
                                DropdownMenuContent,
                                { align: 'end' },
                                {
                                    default: () => [
                                        h(DropdownMenuLabel, {}, () => 'Actions'),
                                        h(DropdownMenuSeparator),

                                        // Consulter (toujours visible)
                                        h(
                                            DropdownMenuItem,
                                            {
                                                onClick: () => router.visit(route('templates.show', template.id)),
                                            },
                                            {
                                                default: () => [
                                                    h(Eye, { class: 'mr-2 h-4 w-4' }),
                                                    h('span', {}, 'Consulter'),
                                                ],
                                            }
                                        ),

                                        // Modifier (si autorisé)
                                        canEdit ? h(
                                            DropdownMenuItem,
                                            {
                                                onClick: () => router.visit(route('batiments.devisestimatif.edit', {
                                                    batiment: template.batiment.id,
                                                    devisestimatif: template.id
                                                })),
                                            },
                                            {
                                                default: () => [
                                                    h(Pencil, { class: 'mr-2 h-4 w-4' }),
                                                    h('span', {}, 'Modifier'),
                                                ],
                                            }
                                        ) : null,

                                        // Supprimer (si autorisé)
                                        canEdit ? [
                                            h(DropdownMenuSeparator),
                                            h(
                                                DropdownMenuItem,
                                                {
                                                    onClick: () => onDelete(template),
                                                    class: 'text-red-600 dark:text-red-400 focus:text-red-600 focus:bg-red-50 dark:focus:bg-red-950/20',
                                                },
                                                {
                                                    default: () => [
                                                        h(Trash2, { class: 'mr-2 h-4 w-4' }),
                                                        h('span', {}, 'Supprimer'),
                                                    ],
                                                }
                                            )
                                        ] : null,
                                    ].filter(Boolean), // Filtre les éléments null
                                }
                            ),
                        ],
                    }
                )
            },
        },
    ]
}