import { ColumnDef } from "@tanstack/vue-table"
import { h } from "vue"
import { Button } from "@/components/ui/button"
import { ArrowUpDown } from "lucide-vue-next"

export interface User {
  id: number
  name: string
  email: string
  created_at: string
}

export const userColumns: ColumnDef<User>[] = [
  { accessorKey: "id", header: "ID" },
  {
    accessorKey: "name",
    header: "Nom",
    cell: ({ row }) => h("div", {}, row.getValue("name")),
  },
  {
    accessorKey: "email",
    header: ({ column }) =>
      h(Button, { variant: "ghost", onClick: () => column.toggleSorting(column.getIsSorted() === "asc") }, () => [
        "Email",
        h(ArrowUpDown, { class: "ml-2 h-4 w-4" }),
      ]),
    cell: ({ row }) => h("div", { class: "lowercase" }, row.getValue("email")),
  },
  {
    accessorKey: "created_at",
    header: "Créé le",
    cell: ({ row }) => {
      const date = new Date(row.getValue("created_at"))
      return h("div", {}, date.toLocaleDateString())
    },
  },
]
