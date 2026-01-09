<script setup lang="ts">
import { ref, computed } from "vue"
import { useForm, Head } from "@inertiajs/vue3"

import AdminLayout from "@/components/layout/AdminLayout.vue"
import SidebarProvider from "@/components/layout/SidebarProvider.vue"
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue"

import { Button } from "@/components/ui/button"
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

const props = defineProps<{
  user: {
    id: number
    name: string
    email: string
    roles: Array<{
      id: number
      name: string
      permissions: Array<{ id: number; name: string }>
    }>
  }
  roles: Array<{
    id: number
    name: string
  }>
}>()

const popoverOpen = ref(false)

//  Pré-remplir avec les rôles existants
const form = useForm({
  roles: props.user.roles.map(r => r.id),
})

function toggleRole(roleId: number) {
  if (form.roles.includes(roleId)) {
    form.roles = form.roles.filter(id => id !== roleId)
  } else {
    form.roles.push(roleId)
  }
}

const selectedRoles = computed(() =>
  props.roles.filter(r => form.roles.includes(r.id))
)

const submit = () => {
  popoverOpen.value = false
  form.put(route("organisations.users.update", props.user.id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Modifier les rôles de l’utilisateur" />

  <SidebarProvider>
    <AdminLayout>
      <PageBreadcrumb :pageTitle="`Modifier les rôles – ${props.user.name}`" />

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

        <!-- Sélection des rôles -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Rôles dans l’organisation
          </label>

          <Popover v-model:open="popoverOpen">
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
                     :value="role.id"
                      v-for="role in props.roles"
                      :key="role.id"
                           
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
          <div class="flex flex-col gap-4 pt-2">
            <div
              v-for="role in props.user.roles"
              :key="role.id"
              class="flex flex-col gap-2"
            >
              <span class="text-xs font-semibold">{{ role.name }}</span>
              <div class="flex flex-wrap gap-2">
                <Badge
                  v-for="perm in role.permissions"
                  :key="perm.id"
                  variant="secondary"
                >
                  {{ perm.name }}
                </Badge>
                <span
                  v-if="role.permissions.length === 0"
                  class="text-xs text-muted-foreground"
                >
                  Aucune permission
                </span>
              </div>
            </div>
          </div>

          <p v-if="form.errors.roles" class="text-sm text-red-600">
            {{ form.errors.roles }}
          </p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4">
          <Button
            :disabled="form.processing || form.roles.length === 0"
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
