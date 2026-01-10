<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
// shadcn/ui
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverTrigger, PopoverContent } from '@/components/ui/popover';
import {
  Command,
  CommandInput,
  CommandList,
  CommandEmpty,
  CommandGroup,
  CommandItem,
} from '@/components/ui/command';
import { Badge } from '@/components/ui/badge';
import { Check, ChevronsUpDown } from 'lucide-vue-next';

// layout
import AdminLayout from '@/components/layout/AdminLayout.vue';
import SidebarProvider from '@/components/layout/SidebarProvider.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';

// -----------------------------
// Types
// -----------------------------
interface Permission {
  id: number;
  name: string;
}

interface PageProps {
  systemPermissions: Permission[];
  orgPermissions: Permission[];
}
// @ts-ignore
const page = usePage<PageProps>();
console.log(page.props);

// -----------------------------
// UI
// -----------------------------
const popoverOpen = ref(false);

// -----------------------------
// Form
// -----------------------------
const form = useForm({
  category: 'system' as 'system' | 'org',
  name: '',
  permissions: [] as number[],
});

// -----------------------------
// Permissions visibles (STRICT)
// -----------------------------
const visiblePermissions = computed(() => {
  return form.category === 'system'
    ? page.props.systemPermissions
    : page.props.orgPermissions;
});

//  reset dès qu’on change de catégorie
const onCategoryChange = () => {
  form.permissions = [];
  popoverOpen.value = false;
};

// -----------------------------
// Préfixe
// -----------------------------
const ROLE_PREFIX = computed(() =>
  form.category === 'system' ? 'SYSTEM_' : 'ORG_'
);

// -----------------------------
// Nom normalisé
// -----------------------------
const normalizedRoleName = computed({
  get() {
    return form.name;
  },
  set(value: string) {
    let clean = value
      .toUpperCase()
      .replace(/\s+/g, '_')
      .replace(/[^A-Z0-9_]/g, '');

    if (!clean.startsWith(ROLE_PREFIX.value)) {
      clean = ROLE_PREFIX.value + clean.replace(/^SYSTEM_|^ORG_/, '');
    }

    form.name = clean;
  },
});

// -----------------------------
// Permissions
// -----------------------------
const togglePermission = (id: number) => {
  form.permissions = form.permissions.includes(id)
    ? form.permissions.filter(pid => pid !== id)
    : [...form.permissions, id];
};

const selectedPermissions = computed(() =>
  visiblePermissions.value.filter(p =>
    form.permissions.includes(p.id)
  )
);



const submit = () => {
  form.post(route('roles.store'), {
    preserveScroll: true,
  });
};

</script>
<template>
  <SidebarProvider>
    <AdminLayout>
      <PageBreadcrumb pageTitle="Créer un rôle" />

      <div class="mx-auto max-w-3xl space-y-6">

        <!-- Catégorie -->
        <div class="space-y-1">
          <Label>Catégorie du rôle</Label>
          <div class="flex gap-6">
            <label class="flex items-center gap-2">
              <input
                type="radio"
                value="system"
                v-model="form.category"
                @change="onCategoryChange"
              />
              Rôle système
            </label>

            <label class="flex items-center gap-2">
              <input
                type="radio"
                value="org"
                v-model="form.category"
                @change="onCategoryChange"
              />
              Rôle organisation
            </label>
          </div>
        </div>

        <!-- Nom -->
        <div class="space-y-1">
          <Label for="name">Nom du rôle</Label>
          <Input
            id="name"
            v-model="normalizedRoleName"
            placeholder="Ex : ADMIN"
              :class="form.errors.name ? 'border-red-500' : ''"
          />
          <p class="text-xs text-muted-foreground">
            Préfixe appliqué automatiquement :
            <strong>{{ ROLE_PREFIX }}</strong>
          </p>
            <p v-if="form.errors.name" class="text-xs text-red-500">
            {{ form.errors.name }}
          </p>
        </div>

        <!-- Permissions -->
        <div class="space-y-2">
          <Label>
            Permissions autorisées
            <span class="text-xs text-muted-foreground">
              ({{ form.category.toUpperCase() }})
            </span>
          </Label>

          <Popover v-model:open="popoverOpen">
            <PopoverTrigger as-child>
              <Button variant="outline" class="w-full justify-between">
                <span>
                  {{
                    form.permissions.length
                      ? `${form.permissions.length} permissions sélectionnées`
                      : 'Sélectionner des permissions'
                  }}
                </span>
                <ChevronsUpDown class="h-4 w-4 opacity-50" />
              </Button>
            </PopoverTrigger>

            <PopoverContent class="w-[420px] p-0">
              <Command>
                <CommandInput placeholder="Rechercher une permission..." />
                <CommandList>
                  <CommandEmpty>Aucune permission trouvée</CommandEmpty>
                  <CommandGroup heading="Permissions disponibles">
                    <CommandItem
                      v-for="permission in visiblePermissions"
                      :key="permission.id"
                        :value="permission.id"     
                      @select="togglePermission(permission.id)"
                    >
                      <Check
                        class="mr-2 h-4 w-4"
                        :class="form.permissions.includes(permission.id)
                          ? 'opacity-100'
                          : 'opacity-0'"
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
             <p v-if="form.errors.permissions" class="text-xs text-red-500">
            {{ form.errors.permissions }}
          </p>
        </div>
<!-- Validation -->
<div class="pt-6">
  <Button
    class="w-full"
    :disabled="
      form.processing ||
      !form.name ||
      form.permissions.length === 0
    "
    @click="submit"
  >
    <span v-if="form.processing">Création...</span>
    <span v-else>Créer le rôle</span>
  </Button>

  <p class="mt-2 text-center text-xs text-muted-foreground">
    Le rôle sera créé avec
    <strong> {{ form.permissions.length }} </strong>
    permission(s)
  </p>
</div>

      </div>
    </AdminLayout>
  </SidebarProvider>
</template>
