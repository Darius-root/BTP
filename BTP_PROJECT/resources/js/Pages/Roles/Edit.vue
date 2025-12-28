<script setup lang="ts">
import { ref, computed } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { router, Head  } from "@inertiajs/vue3";
import { ArrowLeft } from "lucide-vue-next";
// shadcn/ui
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    Popover,
    PopoverTrigger,
    PopoverContent,
} from "@/components/ui/popover";
import {
    Command,
    CommandInput,
    CommandList,
    CommandEmpty,
    CommandGroup,
    CommandItem,
} from "@/components/ui/command";
import { Badge } from "@/components/ui/badge";
import { Check, ChevronsUpDown } from "lucide-vue-next";

import AdminLayout from "@/components/layout/AdminLayout.vue";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";

// -----------------------------
// Types
// -----------------------------
interface Permission {
    id: number | string;
    name: string;
}

interface Role {
    id: number | string;
    name: string;
    permissions: number[];
    category: "system" | "org";
}

interface PageProps {
    role: Role;
    permissions: Permission[];
    flash?: { success?: string };
}

const page = usePage<PageProps>();
const popoverOpen = ref(false);

// -----------------------------
// Préfixe selon la catégorie
// -----------------------------
const ROLE_PREFIX = computed(() =>
    page.props.role.category === "system" ? "SYSTEM_" : "ORG_"
);

// -----------------------------
// Form Inertia
// -----------------------------
const form = useForm({
    name: page.props.role.name,
    permissions: page.props.role.permissions.map((id) => Number(id)),
});

// -----------------------------
// Normalisation du nom (FORCÉ)
// -----------------------------
const normalizedRoleName = computed({
    get() {
        return form.name;
    },
    set(value: string) {
        let clean = value
            .toUpperCase()
            .replace(/\s+/g, "_")
            .replace(/[^A-Z0-9_]/g, "");

        if (!clean.startsWith(ROLE_PREFIX.value)) {
            clean = ROLE_PREFIX.value + clean.replace(/^SYSTEM_|^ORG_/, "");
        }

        form.name = clean;
    },
});

// -----------------------------
// Permissions
// -----------------------------
const togglePermission = (id: number | string) => {
    const numId = Number(id);
    form.permissions = form.permissions.includes(numId)
        ? form.permissions.filter((pid) => pid !== numId)
        : [...form.permissions, numId];
};

const selectedPermissions = computed(() =>
    page.props.permissions.filter((p) =>
        form.permissions.includes(Number(p.id))
    )
);

// -----------------------------
// Submit
// -----------------------------
const submit = () => {
    popoverOpen.value = false;
    form.put(route("roles.update", page.props.role.id), {
       
    });
};
</script>

<template>
      <Head title="Rôle_modification " />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb pageTitle="Modification du rôle" />
<div class="rounded-2xl border bg-white p-6 dark:bg-white/3">
                <div class="mb-4">
                    <Button variant="outline" @click="router.visit(route('roles.show', page.props.role.id))">
                        <arrowLeft class="w-4 h-4 ml-2 mb" />
                        Retour
                    </Button>
                </div>
            <div class="mx-auto max-w-3xl space-y-6">
                <!-- Nom du rôle -->
                <div class="space-y-1">
                    <Label for="name">Nom du rôle</Label>
                    <Input
                        id="name"
                        v-model="normalizedRoleName"
                        placeholder="Ex : ADMIN"
                    />
                    <p class="text-xs text-muted-foreground">
                        Préfixe appliqué automatiquement :
                        <strong>{{ ROLE_PREFIX }}</strong>
                    </p>
                    <p v-if="form.errors.name" class="text-sm text-red-500">
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Permissions -->
                <div class="space-y-2">
                    <Label
                        >Permissions (catégorie :
                        {{ page.props.role.category }})</Label
                    >

                    <!-- Popover -->
                    <Popover v-model:open="popoverOpen">
                        <PopoverTrigger as-child>
                            <Button
                                variant="outline"
                                class="w-full justify-between"
                            >
                                <span
                                    >{{}}
                  {{
                    form.permissions.length
                      ? `${form.permissions.length} permissions sélectionnées`
                      : 'Sélectionner des permissions'
                                    }}
                                </span>
                                <ChevronsUpDown class="h-4 w-4 opacity-50" />
                            </Button>
                        </PopoverTrigger>

                        <PopoverContent class="w-[400px] p-0">
                            <Command>
                                <CommandInput
                                    placeholder="Rechercher une permission..."
                                />
                                <CommandList>
                                    <CommandEmpty
                                        >Aucune permission trouvée</CommandEmpty
                                    >
                                    <CommandGroup heading="Permissions">
                                    
                                        <CommandItem
                                            v-for="permission in page.props
                                                .permissions"
                                            :key="permission.id"
                                            :value="permission.id"
                                            @select="
                                                togglePermission(permission.id)
                                            "
                                        >
                                            <Check
                                                class="mr-2 h-4 w-4"
                                                :class="
                                                    form.permissions.includes(
                                                        Number(permission.id)
                                                    )
                                                        ? 'opacity-100'
                                                        : 'opacity-0'
                                                "
                                            />
                                            {{ permission.name }}
                                        </CommandItem>
                                    </CommandGroup>
                                </CommandList>
                            </Command>
                        </PopoverContent>
                    </Popover>

                    <!-- Aperçu des permissions sélectionnées -->
                    <div class="flex flex-wrap gap-2 pt-2">
                        <Badge
                            v-for="permission in selectedPermissions"
                            :key="permission.id"
                            variant="secondary"
                        >
                            {{ permission.name }}
                        </Badge>
                    </div>

                    <p
                        v-if="form.errors.permissions"
                        class="text-sm text-red-500"
                    >
                        {{ form.errors.permissions }}
                    </p>
                </div>

                <!-- Submit -->
                <Button
                    class="w-full"
                    :disabled="form.processing"
                    @click="submit"
                >
                    <span v-if="form.processing">Enregistrement...</span>
                    <span v-else>Enregistrer</span>
                </Button>
            </div></div>
        </AdminLayout>
    </SidebarProvider>
</template>
