<script setup lang="ts">
import { ref } from "vue";
import { Head } from "@inertiajs/vue3";

import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";

import AdminLayout from "@/components/layout/AdminLayout.vue";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";

// shadcn/ui
import {
  Tabs,
  TabsList,
  TabsTrigger,
  TabsContent,
} from "@/components/ui/tabs";

defineProps<{
  mustVerifyEmail: boolean;
  status?: string;
}>();

const currentPageTitle = ref("Profil");
</script>

<template>
  <Head title="Profil" />

  <SidebarProvider>
    <AdminLayout>
      <PageBreadcrumb :pageTitle="currentPageTitle" />

      <div
        class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6"
      >
        <Tabs default-value="profile" class="w-full">
          <!-- Onglets -->
          <TabsList class="grid w-full grid-cols-3">
            <TabsTrigger value="profile">
              Informations
            </TabsTrigger>
            <TabsTrigger value="password">
              Mot de passe
            </TabsTrigger>
            <TabsTrigger value="danger">
              Sécurité
            </TabsTrigger>
          </TabsList>

          <!-- Contenu : Profil -->
          <TabsContent value="profile" class="mt-6">
            <UpdateProfileInformationForm
              :must-verify-email="mustVerifyEmail"
              :status="status"
              class="max-w-xl"
            />
          </TabsContent>

          <!-- Contenu : Mot de passe -->
          <TabsContent value="password" class="mt-6">
            <UpdatePasswordForm class="max-w-xl" />
          </TabsContent>

          <!-- Contenu : Suppression -->
          <TabsContent value="danger" class="mt-6">
            <DeleteUserForm class="max-w-xl" />
          </TabsContent>
        </Tabs>
      </div>
    </AdminLayout>
  </SidebarProvider>
</template>
