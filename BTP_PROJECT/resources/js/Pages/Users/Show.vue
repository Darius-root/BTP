<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import { Badge } from "@/components/ui/badge";

defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        created_at: string;
        roles: Array<{
            name: string;
            permissions: Array<{ name: string }>;
        }>;
    };
}>();
</script>

<template>
    <Head :title="`Utilisateur - ${user.name}`" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="`Utilisateur : ${user.name}`" />

            <div class="mx-auto max-w-3xl space-y-6">
                <!-- Infos générales -->
                <div class="rounded-xl border bg-white p-4 space-y-2">
                    <p><strong>Nom :</strong> {{ user.name }}</p>
                    <p><strong>Email :</strong> {{ user.email }}</p>
                    <p><strong>Créé le :</strong> {{ user.created_at }}</p>
                </div>

                <!-- Rôles & permissions -->
                <div
                    v-for="role in user.roles"
                    :key="role.name"
                    class="rounded-xl border bg-white p-4 space-y-2"
                >
                    <h3 class="font-semibold">{{ role.name }}</h3>

                    <div class="flex flex-wrap gap-2">
                        <Badge
                            v-for="perm in role.permissions"
                            :key="perm.name"
                            variant="secondary"
                        >
                            {{ perm.name }}
                        </Badge>
                    </div>
                </div>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>
