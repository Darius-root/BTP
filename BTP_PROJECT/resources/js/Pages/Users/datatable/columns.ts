import type { ColumnDef } from '@tanstack/vue-table'
import { h } from 'vue'
import { Button } from '@/components/ui/button'
import { Eye, PenBox, Trash2 } from 'lucide-vue-next'
import { router } from "@inertiajs/vue3";

import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip'

export interface User {
  id: number
  name: string
  email: string
  created_at: string
}


export const userColumns: ColumnDef<User>[] = [
/* ID */
{
  accessorKey: 'id',
  header: 'N°',
  enableSorting: false, // Désactive le tri si nécessaire
  cell: ({ row, table }) => {
    const index = table.getState().pagination.pageIndex * table.getState().pagination.pageSize + row.index + 1;
    return h('span', { class: 'font-medium'}, index);
  },
},


  /* NAME */
  {
    accessorKey: 'name',
    header: 'Nom',
    cell: ({ row }) =>
      h('span', { class: 'font-medium' }, row.getValue('name')),
  },

  /* EMAIL */
  {
    accessorKey: 'email',
    header: 'Email',
    cell: ({ row }) =>
      h('span', { class: 'lowercase text-muted-foreground' }, row.getValue('email')),
  },

  /* CREATED AT */
  {
    accessorKey: 'created_at',
    header: 'Créé le',
    cell: ({ row }) => {
      const date = new Date(row.getValue('created_at'))
      return h(
        'span',
        { class: 'text-sm text-muted-foreground' },
        date.toLocaleDateString()
      )
    },
  },

  /* ACTIONS */
  {
    id: 'actions',
    header: 'Actions',
    enableHiding: false,
    enableSorting: false,
    cell: ({ row }) => {
      const user = row.original

      const ActionButton = (
        icon: any,
        color: string,
        tooltip: string,
        onClick: () => void
      ) =>
        h(TooltipProvider, {}, () =>
          h(Tooltip, {}, () => [
            h(TooltipTrigger, { asChild: true }, () =>
              h(
                Button,
                {
                  size: 'icon',
                  variant: 'secondary',
                  class: color,
                  onClick,
                },
                () => h(icon, { class: 'w-4 h-4' })
              )
            ),
            h(TooltipContent, {}, tooltip),
          ])
        )

      return h(
        'div',
        { class: 'flex items-center gap-1' },
        [
          ActionButton(
            Eye,
            'text-green-600',
            'Voir l’utilisateur',
            () => router.get(`/users/${user.id}`)
          ),
          // ActionButton(
          //   PenBox,
          //   'text-yellow-600',
          //   'Modifier l’utilisateur',
          //   () => console.log('Modifier', user.id)
          // ),
          ActionButton(
            Trash2,
            'text-red-600',
            'Supprimer l’utilisateur',
            () => router.get(`/users/${user.id}`)
          ),
        ]
      )
    },
  },
]
