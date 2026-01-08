<script setup lang="ts">
import { useForm, Head } from "@inertiajs/vue3";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";

import { Button } from "@/components/ui/button";
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from "@/components/ui/select";

const props = defineProps<{
  user: {
    id: number;
    name: string;
    email: string;
  };
  currentRoleId: number;
  roles: Array<{
    id: number;
    name: string;
  }>;
}>();

const form = useForm({
  role_id: props.currentRoleId,
});

const submit = () => {
  form.put(route("organisations.users.update", props.user.id), {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Modifier le rôle de l’utilisateur" />

  <SidebarProvider>
    <AdminLayout>
      <PageBreadcrumb
        :pageTitle="`Modifier le rôle – ${props.user.name}`"
      />

      <div
        class="mx-auto max-w-2xl space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-black"
      >
        <!-- Infos utilisateur -->
        <div class="space-y-1">
          <p class="text-sm text-gray-600 dark:text-gray-400">
            <span class="font-medium text-gray-900 dark:text-gray-100">
              Utilisateur :
            </span>
            {{ props.user.name }}
          </p>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ props.user.email }}
          </p>
        </div>

        <!-- Sélection du rôle -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Rôle dans l’organisation
          </label>

          <Select v-model="form.role_id">
            <SelectTrigger>
              <SelectValue placeholder="Sélectionner un rôle" />
            </SelectTrigger>

            <SelectContent>
              <SelectItem
                v-for="role in props.roles"
                :key="role.id"
                :value="role.id"
              >
                {{ role.name }}
              </SelectItem>
            </SelectContent>
          </Select>

          <p v-if="form.errors.role_id" class="text-sm text-red-600">
            {{ form.errors.role_id }}
          </p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4">
          <Button
            :disabled="form.processing || !form.role_id"
            @click="submit"
          >
            <span v-if="form.processing">Mise à jour...</span>
            <span v-else>Mettre à jour</span>
          </Button>
        </div>
      </div>
    </AdminLayout>
  </SidebarProvider>
</template>
