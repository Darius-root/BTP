<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3"
import { ref, watch } from "vue"

import AdminLayout from "@/components/layout/AdminLayout.vue"
import SidebarProvider from "@/components/layout/SidebarProvider.vue"
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue"

import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"

defineProps<{
  organisations: Array<{ id: number; nom: string }>
  roles: Array<{ id: number; name: string }>
}>()

const organisationId = ref<number | null>(null)
const selectedRole = ref<string>("")
const userSearch = ref("")
const selectedUser = ref<any>(null)
const users = ref<Array<any>>([])
const loading = ref(false)

/* 🔎 Recherche utilisateur */
watch(userSearch, async (value) => {
  if (value.length < 3) return

  loading.value = true
  const res = await fetch(`/users/search?query=${value}`)
  users.value = await res.json()
  loading.value = false
})

function submit() {
  if (!organisationId.value || !selectedUser.value || !selectedRole.value) return

  router.post(route("organisations.users.store"), {
    organisation_id: organisationId.value,
    user_id: selectedUser.value.id,
    role: selectedRole.value,
  })
}
</script>

<template>
  <Head title="Ajouter un utilisateur à une organisation" />

  <SidebarProvider>
    <AdminLayout>
      <PageBreadcrumb pageTitle="Ajouter un utilisateur" />

      <div class="mx-auto max-w-xl">
        <Card>
          <CardHeader>
            <CardTitle>Ajouter un utilisateur à une organisation</CardTitle>
          </CardHeader>

          <CardContent class="space-y-6">
            <!-- Organisation -->
            <div class="space-y-2">
              <Label>Organisation</Label>
              <Select v-model="organisationId">
                <SelectTrigger>
                  <SelectValue placeholder="Sélectionner une organisation" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="org in organisations"
                    :key="org.id"
                    :value="org.id"
                  >
                    {{ org.nom }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Recherche utilisateur -->
            <div class="space-y-2">
              <Label>Utilisateur</Label>
              <Input
                v-model="userSearch"
                placeholder="Rechercher par nom ou email..."
              />

              <div
                v-if="users.length"
                class="border rounded-md max-h-40 overflow-auto"
              >
                <div
                  v-for="u in users"
                  :key="u.id"
                  class="px-3 py-2 cursor-pointer hover:bg-muted"
                  @click="selectedUser = u; users = []; userSearch = u.email"
                >
                  <p class="text-sm font-medium">{{ u.name }}</p>
                  <p class="text-xs text-muted-foreground">{{ u.email }}</p>
                </div>
              </div>
            </div>

            <!-- Rôle -->
            <div class="space-y-2">
              <Label>Rôle</Label>
              <Select v-model="selectedRole">
                <SelectTrigger>
                  <SelectValue placeholder="Sélectionner un rôle" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="role in roles"
                    :key="role.id"
                    :value="role.name"
                  >
                    {{ role.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2 pt-4">
              <Button
                variant="outline"
                @click="router.visit(route('organisations.index'))"
              >
                Annuler
              </Button>
              <Button @click="submit">
                Ajouter
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  </SidebarProvider>
</template>
