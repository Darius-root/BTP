<script setup lang="ts">
import AdminLayout from "@/components/layout/AdminLayout.vue";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  CardAction,
} from "@/components/ui/card";
import {
  Table,
  TableHeader,
  TableRow,
  TableHead,
  TableCell,
  TableBody,
} from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { router, Head } from "@inertiajs/vue3";
import { ref } from "vue";
import {
  EyeIcon,
  PenBoxIcon,
  TrashIcon,
  PlusIcon,
  ChevronDownIcon,
  ChevronUpIcon,
} from "lucide-vue-next";

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
} from "@/components/ui/alert-dialog";

const currentPageTitle = ref("Gestion des rôles organisationnels");

const props = defineProps({
  orgRoles: Array as () => Array<{
    id: number;
    name: string;
    readonly: boolean;
    permissions: Array<{ id: number; name: string }>;
  }>,
});

const goToCreate = () => router.visit(route("organisations.roles.create"));
const editRole = (role: any) =>
  !role.readonly && router.visit(route("organisations.roles.edit", role.id));

const deleteRole = (role: any) => {
  router.delete(route("organisations.roles.destroy", role.id));
};

// Dropdown permissions
const openDropdown = ref<number | null>(null);
const toggleDropdown = (roleId: number) => {
  openDropdown.value = openDropdown.value === roleId ? null : roleId;
};
</script>

<template>
  <Head title="Gestion des rôles organisationnels" />
  <SidebarProvider>
    <AdminLayout>
      <PageBreadcrumb :pageTitle="currentPageTitle" />

      <div
        class="rounded-2xl border  p-5 dark:border-gray-800 dark:bg-black lg:p-6"
      >
        <div class="p-6 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Rôles Organisationnels</CardTitle>
              <CardAction>
                <Button variant="outline" @click="goToCreate">
                  Ajouter un rôle organisationnel
                  <PlusIcon class="w-4 h-4 ml-2 text-blue-600" />
                </Button>
              </CardAction>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Nom du rôle</TableHead>
                    <TableHead>Permissions</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="role in orgRoles" :key="role.id">
                    <TableCell>{{ role.name }}</TableCell>

                    <!-- Dropdown Permissions -->
                    <TableCell>
                      <div class="relative inline-block">
                        <Button
                          size="sm"
                          variant="outline"
                          @click="toggleDropdown(role.id)"
                          class="flex items-center gap-2"
                        >
                          Permissions ({{ role.permissions.length }})
                          <ChevronDownIcon
                            v-if="openDropdown !== role.id"
                            class="w-4 h-4"
                          />
                          <ChevronUpIcon v-else class="w-4 h-4" />
                        </Button>

                        <div
                          v-show="openDropdown === role.id"
                          class="absolute z-10 mt-1 w-64 rounded-md border bg-white shadow-lg p-2 max-h-60 overflow-y-auto"
                        >
                          <ul class="space-y-1">
                            <li
                              v-for="perm in role.permissions"
                              :key="perm.id"
                              class="text-sm"
                            >
                              {{ perm.name }}
                            </li>
                          </ul>
                        </div>
                      </div>
                    </TableCell>

                    <!-- Actions -->
                    <TableCell class="text-right">
                      <div class="flex justify-end space-x-2">
                        <!-- Modifier -->
                        <Button
                          size="sm"
                          variant="secondary"
                          @click="editRole(role)"
                          :disabled="role.readonly"
                        >
                          <PenBoxIcon class="w-4 h-4 text-yellow-600" />
                        </Button>

                        <!-- Supprimer avec AlertDialog -->
                        <AlertDialog>
                          <AlertDialogTrigger as-child>
                            <Button
                              size="sm"
                              variant="secondary"
                              :disabled="role.readonly"
                            >
                              <TrashIcon class="w-4 h-4 text-red-600" />
                            </Button>
                          </AlertDialogTrigger>
                          <AlertDialogContent>
                            <AlertDialogHeader>
                              <AlertDialogTitle>
                                Supprimer le rôle
                              </AlertDialogTitle>
                              <AlertDialogDescription>
                                Êtes-vous sûr de vouloir supprimer le rôle
                                <strong> {{ role.name }} </strong> ?
                                Cette action est irréversible.
                              </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                              <AlertDialogCancel>Annuler</AlertDialogCancel>
                              <AlertDialogAction
                                @click="deleteRole(role)"
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
            </CardContent>
          </Card>
        </div>
      </div>
    </AdminLayout>
  </SidebarProvider>
</template>
