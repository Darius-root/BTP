<script setup lang="ts">
import { Head, router, useForm, usePage } from "@inertiajs/vue3"
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
  roles: Array<{ id: number; name: string }>
}>()

const page = usePage()

const form = useForm({
  email: "",
  role: "",
})

function submit() {
  form.post(route("organisations.users.store"), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset("email", "role")
    },
  })
}
</script>

<template>
  <Head title="Ajouter un utilisateur" />

  <SidebarProvider>
    <AdminLayout>
      <PageBreadcrumb pageTitle="Ajouter un utilisateur" />

      <div class="mx-auto max-w-xl">
        <Card>
          <CardHeader>
            <CardTitle>Ajouter un utilisateur à l’organisation active</CardTitle>
          </CardHeader>

          <CardContent class="space-y-6">
          

            <!-- EMAIL -->
            <div class="space-y-2">
              <Label>Adresse email</Label>
              <Input
                v-model="form.email"
                type="email"
                placeholder="ex: utilisateur@email.com"
              />
              <p v-if="form.errors.email" class="text-sm text-red-500">
                {{ form.errors.email }}
              </p>
            </div>

            <!-- ROLE -->
            <div class="space-y-2">
              <Label>Rôle</Label>
              <Select v-model="form.role">
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
              <p v-if="form.errors.role" class="text-sm text-red-500">
                {{ form.errors.role }}
              </p>
            </div>

            <!-- ACTIONS -->
            <div class="flex justify-end gap-2 pt-4">
              <Button
                variant="outline"
                @click="router.visit(route('organisations.index'))"
              >
                Annuler
              </Button>

              <Button
                :disabled="form.processing"
                @click="submit"
              >
                {{ form.processing ? "Ajout en cours..." : "Ajouter" }}
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  </SidebarProvider>
</template>
