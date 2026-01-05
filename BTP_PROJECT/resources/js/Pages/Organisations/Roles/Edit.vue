<script setup lang="ts">
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from '@/components/ui/badge';
import { Label } from "@/components/ui/label";
import { Popover, PopoverTrigger, PopoverContent } from "@/components/ui/popover";
import {
  Command,
  CommandInput,
  CommandList,
  CommandEmpty,
  CommandGroup,
  CommandItem,
} from "@/components/ui/command";
import { Check, ChevronsUpDown } from "lucide-vue-next";

interface Permission {
  id: number;
  name: string;
}

const props = defineProps<{
  role: { id: number; name: string; permissions: number[] };
  permissions: Permission[];
}>();

const popoverOpen = ref(false);

const form = useForm({
  name: props.role.name,
  permissions: props.role.permissions,
});

// Toggle permission
const togglePermission = (id: number) => {
  form.permissions = form.permissions.includes(id)
    ? form.permissions.filter((pid) => pid !== id)
    : [...form.permissions, id];
};

const selectedPermissions = computed(() =>
  props.permissions.filter((p) => form.permissions.includes(p.id))
);

// Nom normalisé
const ROLE_PREFIX = "ORG_";

const normalizedRoleName = computed({
  get() { return form.name; },
  set(value: string) {
    let clean = value.toUpperCase().replace(/\s+/g, "_").replace(/[^A-Z0-9_]/g, "");
    if (!clean.startsWith(ROLE_PREFIX)) {
      clean = ROLE_PREFIX + clean.replace(/^ORG_/, "");
    }
    form.name = clean;
  },
});

// Submit
const submit = () => {
  form.put(route("organisations.roles.update", props.role.id), { preserveScroll: true });
};
</script>

<template>
  <SidebarProvider>
    <AdminLayout>
      <PageBreadcrumb pageTitle="Modifier le rôle" />

      <div class="mx-auto max-w-3xl space-y-6">

        <!-- Nom -->
        <div class="space-y-1">
          <Label for="name">Nom du rôle</Label>
          <Input id="name" v-model="normalizedRoleName" placeholder="Ex : ADMIN" />
          <p class="text-xs text-muted-foreground">
            Préfixe appliqué automatiquement : <strong>{{ ROLE_PREFIX }}</strong>
          </p>
          <p v-if="form.errors.name" class="text-xs text-red-600">{{ form.errors.name }}</p>
        </div>

        <!-- Permissions -->
        <div class="space-y-2">
          <Label>Permissions autorisées</Label>
          <Popover v-model:open="popoverOpen">
            <PopoverTrigger as-child>
              <Button variant="outline" class="w-full justify-between">
                <span>
                  {{ form.permissions.length
                    ? `${form.permissions.length} permissions sélectionnées`
                    : "Sélectionner des permissions" }}
                </span>
                <ChevronsUpDown class="h-4 w-4 opacity-50" />
              </Button>
            </PopoverTrigger>
            <PopoverContent class="w-[420px] p-0">
              <Command>
                <CommandInput placeholder="Rechercher une permission..." />
                <CommandList>
                  <CommandEmpty>Aucune permission trouvée</CommandEmpty>
                  <CommandGroup heading="Permissions organisation">
                    <CommandItem
                      v-for="permission in props.permissions"
                      :key="permission.id"
                      :value="permission.id"
                      @select="togglePermission(permission.id)"
                    >
                      <Check
                        class="mr-2 h-4 w-4"
                        :class="form.permissions.includes(permission.id) ? 'opacity-100' : 'opacity-0'"
                      />
                      {{ permission.name }}
                    </CommandItem>
                  </CommandGroup>
                </CommandList>
              </Command>
            </PopoverContent>
          </Popover>

          <!-- Aperçu -->
          <div class="flex flex-wrap gap-2 pt-2">
            <Badge
              v-for="permission in selectedPermissions"
              :key="permission.id"
              variant="secondary"
            >
              {{ permission.name }}
            </Badge>
          </div>
        </div>

        <!-- Validation -->
        <div class="pt-6">
          <Button
            class="w-full"
            :disabled="form.processing || !form.name || form.permissions.length === 0"
            @click="submit"
          >
            <span v-if="form.processing">Mise à jour...</span>
            <span v-else>Mettre à jour le rôle</span>
          </Button>
        </div>

      </div>
    </AdminLayout>
  </SidebarProvider>
</template>
