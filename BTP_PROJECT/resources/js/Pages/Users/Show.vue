<script setup lang="ts">
import { Head,router } from "@inertiajs/vue3";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import { Button } from "@/components/ui/button";
import { ArrowLeft } from "lucide-vue-next";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import { Badge } from "@/components/ui/badge";


defineProps<{
    user: any; // user général
    teamsWithRoles: Array<{
        team: { id: number; nom: string };
        roles: string[];
    }>;
}>();
</script>

<template>
  <Head :title="`Utilisateur - ${user.name}`" />

  <SidebarProvider>
    <AdminLayout>
      <PageBreadcrumb :pageTitle="`Utilisateur : ${user.name}`" />
      <div class="rounded-2xl border bg-white p-6 dark:bg-black">

<div class="mb-4">
                    <Button variant="outline" @click="router.visit(route('users.index'))">
                        <arrowLeft class="w-4 h-4 ml-2 mb" />
                        Retour
                    </Button>
                </div>
      <div class="mx-auto max-w-3xl space-y-6">
        <!-- Infos générales -->
        <div class="rounded-xl border bg-white dark:bg-black p-4 space-y-2">
          <p><strong>Nom :</strong> {{ user.name }}</p>
          <p><strong>Email :</strong> {{ user.email }}</p>
          <p><strong>Créé le :</strong> {{ new Date(user.created_at).toLocaleDateString() }}</p>
        </div>

        <!-- Organisations et rôles -->
        <div v-if="teamsWithRoles.length" class="space-y-4 ">
          <div
            v-for="teamData in teamsWithRoles"
            :key="teamData.team.id"
            class="rounded-xl border bg-white dark:bg-black p-4 space-y-2"
          >
            <h3 class="font-semibold">{{ teamData.team.nom }}</h3>

            <div v-if="teamData.roles.length" class="flex flex-wrap gap-2">
              <Badge
                v-for="role in teamData.roles"
                :key="role"
                variant="secondary"
              >
                {{ role }}
              </Badge>
            </div>
            <div v-else>
              <span class="text-sm text-muted-foreground">Aucun rôle attribué</span>
            </div>
          </div>
        </div>
      </div></div>
    </AdminLayout>
  </SidebarProvider>
</template>
