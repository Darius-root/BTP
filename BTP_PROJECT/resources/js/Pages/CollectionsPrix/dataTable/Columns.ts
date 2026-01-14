import type { ColumnDef } from '@tanstack/vue-table'
import { h } from 'vue'
import { Button } from '@/components/ui/button'
import { Eye, Edit, Trash2, CheckCircle, Clock, Package, Ruler, MapPin } from 'lucide-vue-next'
import { router } from "@inertiajs/vue3"
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip'

export interface Collection {
  id: number
  materiau: { nom: string; unite: { libelle: string } }
  description_materiaux: string
  price: number
  devise: { symbole?: string; code: string }
  commune: { libelle: string }
  arrondissement?: { libelle: string }
  quartier_id?: string
  point_vente?: string
  categorie: { intitule: string }
  is_validated: boolean
  user: { id: number; name: string }
  validator?: { name: string }
}

// Helper pour formater les nombres
const formatNumber = (value: number) =>
  new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0)

// Helper pour les couleurs de catégorie
const getCategoryColor = (category: string) => {
  const normalized = category.toUpperCase()
  const colors: Record<string, string> = {
    'GROS-ŒUVRE': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    'SECOND-ŒUVRE': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    'ÉQUIPEMENT': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    'FINITIONS': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    'INSTALLATIONS': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
  }
  return colors[normalized] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

export const createCollectionColumns = (
  isAdmin: boolean,
  onValidate: (collection: Collection) => void,
  onDelete: (collection: Collection) => void
): ColumnDef<Collection>[] => [
  /* N° */
  {
    accessorKey: 'id',
    header: 'N°',
    enableSorting: false,
    cell: ({ row, table }) => {
      const index = table.getState().pagination.pageIndex * table.getState().pagination.pageSize + row.index + 1
      return h('span', { class: 'font-medium text-gray-900 dark:text-gray-100' }, index)
    },
  },

  /* MATÉRIAU */
  {
    accessorKey: 'materiau',
    header: 'Matériau',
    enableSorting: false,
    cell: ({ row }) => {
      const collection = row.original
      return h('div', { class: 'flex items-start gap-3' }, [
        h('div', { class: 'w-10 h-10 flex items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' }, [
          h(Package, { class: 'w-5 h-5' })
        ]),
        h('div', { class: 'flex-1 min-w-0' }, [
          h('div', { class: 'font-medium text-gray-900 dark:text-gray-100 truncate' }, collection.materiau.nom),
          h('div', { class: 'text-xs text-gray-500 dark:text-gray-400 mt-0.5' }, collection.description_materiaux),
          h('div', { class: 'text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-1' }, [
            h(Ruler, { class: 'w-3 h-3' }),
            collection.materiau.unite.libelle
          ])
        ])
      ])
    },
  },

  /* PRIX */
  {
    accessorKey: 'price',
    header: 'Prix',
    enableSorting: true,
    cell: ({ row }) => {
      const collection = row.original
      return h('div', { class: 'space-y-1' }, [
        h('div', { class: 'font-bold text-lg text-green-600 dark:text-green-400' }, formatNumber(collection.price)),
        h('div', { class: 'text-xs text-gray-500 dark:text-gray-400' }, collection.devise.symbole || collection.devise.code)
      ])
    },
  },

  /* LOCALISATION */
  {
    accessorKey: 'commune',
    header: 'Localisation',
    enableSorting: false,
    cell: ({ row }) => {
      const collection = row.original
      return h('div', { class: 'space-y-1' }, [
        h('div', { class: 'flex items-center gap-1 text-sm text-gray-900 dark:text-gray-100' }, [
          h(MapPin, { class: 'w-3 h-3' }),
          collection.commune.libelle
        ]),
        collection.arrondissement && h('div', { class: 'text-xs text-gray-500 dark:text-gray-400' }, collection.arrondissement.libelle),
        collection.quartier_id && h('div', { class: 'text-xs text-gray-500 dark:text-gray-400' }, collection.quartier_id),
        collection.point_vente && h('div', { class: 'text-xs text-blue-600 dark:text-blue-400' }, `📍 ${collection.point_vente}`)
      ])
    },
  },

  /* CORPS D'ÉTAT */
  {
    accessorKey: 'categorie',
    header: "Corps d'état",
    enableSorting: false,
    cell: ({ row }) => {
      const collection = row.original
      return h('div', { 
        class: `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getCategoryColor(collection.categorie.intitule)}` 
      }, collection.categorie.intitule)
    },
  },

  /* STATUT */
  {
    accessorKey: 'is_validated',
    header: 'Statut',
    enableSorting: true,
    cell: ({ row }) => {
      const collection = row.original
      const statusClass = collection.is_validated 
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' 
        : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
      
      return h('span', { class: `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}` }, [
        h(collection.is_validated ? CheckCircle : Clock, { class: 'w-3 h-3 mr-1' }),
        collection.is_validated ? 'Validée' : 'En attente'
      ])
    },
  },

  /* COLLECTEUR (Admin seulement) */
  ...(isAdmin ? [{
    accessorKey: 'user',
    header: 'Collecteur',
    enableSorting: false,
    cell: ({ row }: { row: any }) => {
      const collection = row.original
      return h('div', { class: 'text-sm' }, [
        h('div', { class: 'flex items-center gap-1 text-gray-900 dark:text-gray-100' }, collection.user.name),
        collection.is_validated && collection.validator && 
          h('div', { class: 'text-xs text-gray-500 dark:text-gray-400 mt-1' }, `Validé par: ${collection.validator.name}`)
      ])
    },
  }] : []),

  /* ACTIONS */
  {
    id: 'actions',
    header: 'Actions',
    enableHiding: false,
    enableSorting: false,
    cell: ({ row }) => {
      const collection = row.original
      const currentUserId = collection.user.id // À passer depuis le composant parent

      const canEdit = !collection.is_validated && (isAdmin || collection.user.id === currentUserId)
      const canDelete = isAdmin
      const canValidate = isAdmin && !collection.is_validated

      const ActionButton = (
        icon: any,
        color: string,
        tooltip: string,
        onClick: () => void,
        show = true
      ) => {
        if (!show) return null
        
        return h(TooltipProvider, {}, () =>
          h(Tooltip, {}, () => [
            h(TooltipTrigger, { asChild: true }, () =>
              h(
                Button,
                {
                  size: 'icon',
                  variant: 'ghost',
                  class: `${color} hover:bg-opacity-10`,
                  onClick,
                },
                () => h(icon, { class: 'w-4 h-4' })
              )
            ),
            h(TooltipContent, {}, tooltip),
          ])
        )
      }

      return h(
        'div',
        { class: 'flex items-center gap-1 justify-end' },
        [
          ActionButton(
            Eye,
            'text-gray-700',
            'Voir les détails',
            () => router.get(`/collections-prix/${collection.id}`)
          ),
          ActionButton(
            CheckCircle,
            'text-green-600',
            'Valider la collecte',
            () => onValidate(collection),
            canValidate
          ),
          ActionButton(
            Edit,
            'text-blue-600',
            'Modifier la collecte',
            () => router.get(`/collections-prix/${collection.id}/edit`),
            canEdit
          ),
          ActionButton(
            Trash2,
            'text-red-600',
            'Supprimer la collecte',
            () => onDelete(collection),
            canDelete
          ),
        ].filter(Boolean) // Enlève les null
      )
    },
  },
]