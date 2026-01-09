<script setup lang="ts">
import { Head, router, useForm } from "@inertiajs/vue3"
import AdminLayout from "@/components/layout/AdminLayout.vue"
import SidebarProvider from "@/components/layout/SidebarProvider.vue"
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue"

import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Popover, PopoverTrigger, PopoverContent } from "@/components/ui/popover"
import {
  Command,
  CommandInput,
  CommandList,
  CommandEmpty,
  CommandGroup,
  CommandItem,
} from "@/components/ui/command"
import { Badge } from "@/components/ui/badge"
import { Check, ChevronsUpDown } from "lucide-vue-next"

defineProps<{
  roles: Array<{ id: number; name: string }>
}>()

const form = useForm({
  email: "",
  roles: [] as number[], // tableau d'IDs
})

function toggleRole(roleId: number) {
  if (form.roles.includes(roleId)) {
    form.roles = form.roles.filter(id => id !== roleId)
  } else {
    form.roles.push(roleId)
  }
}

function submit() {
  console.log("Payload envoyé :", form.data())
  form.post(route("organisations.users.store"), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset("email", "roles")
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
              <Label for="email">Adresse email</Label>
              <Input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="ex: utilisateur@email.com"
              />
              <p v-if="form.errors.email" class="text-sm text-red-500">
                {{ form.errors.email }}
              </p>
            </div>

            <!-- ROLES MULTI SELECT avec Command -->
            <div class="space-y-2">
              <Label>Rôles</Label>

              <Popover>
                <PopoverTrigger as-child>
                  <Button variant="outline" class="w-full justify-between">
                    <span>
                      {{
                        form.roles.length
                          ? `${form.roles.length} rôle(s) sélectionné(s)`
                          : "Sélectionner des rôles"
                      }}
                    </span>
                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                  </Button>
                </PopoverTrigger>

                <PopoverContent class="w-[320px] p-0">
                  <Command>
                    <CommandInput placeholder="Rechercher un rôle..." />
                    <CommandList>
                      <CommandEmpty>Aucun rôle trouvé</CommandEmpty>
                      <CommandGroup heading="Rôles disponibles">
                        <CommandItem
                          v-for="role in roles"
                          :key="role.id"
                           :value="role.id"     
                          @select="toggleRole(role.id)"
                        >
                          <Check
                            class="mr-2 h-4 w-4"
                            :class="form.roles.includes(role.id)
                              ? 'opacity-100'
                              : 'opacity-0'"
                          />
                          {{ role.name }}
                        </CommandItem>
                      </CommandGroup>
                    </CommandList>
                  </Command>
                </PopoverContent>
              </Popover>

              <!-- Aperçu sélection -->
              <div class="flex flex-wrap gap-2 pt-2">
                <Badge
                  v-for="roleId in form.roles"
                  :key="roleId"
                  variant="secondary"
                >
                  {{ roles.find(r => r.id === roleId)?.name }}
                </Badge>
              </div>

              <p v-if="form.errors.roles" class="text-sm text-red-500">
                {{ form.errors.roles }}
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
                :disabled="form.processing || !form.email || form.roles.length === 0"
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
