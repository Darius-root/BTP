<script setup lang="ts">
import { ref } from "vue"
import { Head, router } from "@inertiajs/vue3"

import AdminLayout from "@/components/layout/AdminLayout.vue"
import SidebarProvider from "@/components/layout/SidebarProvider.vue"
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue"

import {
  Table,
  TableHeader,
  TableRow,
  TableHead,
  TableCell,
  TableBody,
} from "@/components/ui/table"

import {
  Collapsible,
  CollapsibleTrigger,
  CollapsibleContent,
} from "@/components/ui/collapsible"

import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"

// shadcn/ui AlertDialog
import {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
  AlertDialogAction,
} from "@/components/ui/alert-dialog"

import {
  PenBoxIcon,
  TrashIcon,
  PlusIcon,
  ChevronDownIcon,
} from "lucide-vue-next"

const props = defineProps<{
  orgUsers: Array<{
    id: number
    name: string
    email: string
    role: {
      id: number
      name: string
      readonly: boolean
      permissions: { id: number; name: string }[]
    }
  }>
}>()

const currentPageTitle = ref("Utilisateurs de l’organisation")

const goToCreate = () => {
  router.visit(route("organisations.users.create"))
}

const editUser = (user: any) => {
  if (!user.role.readonly) {
    router.visit(route("organisations.users.edit", user.id))
  }
}

const deleteUser = (user: any) => {
  if (!user.role.readonly) {
    router.delete(route("organisations.users.destroy", user.id))
  }
}
</script>

<template>
  <Head title="Utilisateurs de l’organisation" />

  <SidebarProvider>
    <AdminLayout>
      <PageBreadcrumb :pageTitle="currentPageTitle" />

      <div class="rounded-2xl border bg-white p-6 dark:bg-black">
        <!-- Bouton Ajouter -->
        <div class="flex justify-end mb-4">
          <Button variant="outline" @click="goToCreate">
            Ajouter un utilisateur
            <PlusIcon class="w-4 h-4 ml-2 text-blue-600" />
          </Button>
        </div>

        <!-- Vérification si aucun utilisateur -->
        <div v-if="props.orgUsers.length === 0" class="text-center py-10">
          <p class="text-sm text-muted-foreground">
            Aucun utilisateur ajouté pour cette organisation.
          </p>
        </div>

        <!-- Sinon afficher le tableau -->
        <Table v-else>
          <TableHeader>
            <TableRow>
              <TableHead>Utilisateur</TableHead>
              <TableHead>Rôle</TableHead>
              <TableHead>Permissions</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="user in props.orgUsers" :key="user.id">
              <!-- User -->
              <TableCell>
                <div class="flex flex-col">
                  <span class="font-medium">{{ user.name }}</span>
                  <span class="text-xs text-muted-foreground">
                    {{ user.email }}
                  </span>
                </div>
              </TableCell>

              <!-- Role -->
              <TableCell>
                <Badge
                  :variant="user.role.readonly ? 'secondary' : 'default'"
                >
                  {{ user.role.name }}
                </Badge>
              </TableCell>

              <!-- Permissions -->
              <TableCell class="max-w-sm">
                <Collapsible>
                  <CollapsibleTrigger
                    class="flex items-center gap-2 text-sm text-muted-foreground"
                  >
                    Voir permissions
                    <ChevronDownIcon class="h-4 w-4" />
                  </CollapsibleTrigger>

                  <CollapsibleContent>
                    <div class="mt-2 flex flex-wrap gap-2">
                      <Badge
                        v-for="perm in user.role.permissions"
                        :key="perm.id"
                        variant="outline"
                      >
                        {{ perm.name }}
                      </Badge>

                      <span
                        v-if="user.role.permissions.length === 0"
                        class="text-xs text-muted-foreground"
                      >
                        Aucune permission
                      </span>
                    </div>
                  </CollapsibleContent>
                </Collapsible>
              </TableCell>

              <!-- Actions -->
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <!-- Modifier -->
                  <Button
                    size="sm"
                    variant="secondary"
                    :disabled="user.role.readonly"
                    @click="editUser(user)"
                  >
                    <PenBoxIcon class="h-4 w-4 text-yellow-600" />
                  </Button>

                  <!-- Supprimer avec AlertDialog -->
                  <AlertDialog>
                    <AlertDialogTrigger as-child>
                      <Button
                        size="sm"
                        variant="secondary"
                        :disabled="user.role.readonly"
                      >
                        <TrashIcon class="h-4 w-4 text-red-600" />
                      </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                      <AlertDialogHeader>
                        <AlertDialogTitle>
                          Retirer l’utilisateur
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                          Êtes-vous sûr de vouloir retirer
                          <strong>{{ user.name }}</strong> de l’organisation ?
                          Cette action est irréversible.
                        </AlertDialogDescription>
                      </AlertDialogHeader>
                      <AlertDialogFooter>
                        <AlertDialogCancel>Annuler</AlertDialogCancel>
                        <AlertDialogAction
                          @click="deleteUser(user)"
                          class="bg-red-600 text-white hover:bg-red-700"
                        >
                          Supprimer
                        </AlertDialogAction>
                      </AlertDialogFooter>
                    </AlertDialogContent>
                  </AlertDialog>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </AdminLayout>
  </SidebarProvider>
</template>

