<script setup>
import AdminLayout from "@/components/layout/AdminLayout.vue";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { ref } from "vue";

const currentPageTitle = ref("Permissions");

import { Badge } from "@/components/ui/badge";

import { useForm } from "@inertiajs/vue3";

defineProps({
   
    systemPermissions: Array,
    orgPermissions: Array,
});

// Formulaire pour créer un rôle
const form = useForm({
    type: "SYSTEM",
    name: "",
});

const submit = () => {
    form.post(route("roles.permissions.store"));
};
</script>

<template>
    <Head title="Gestion des rôles" />
    <SidebarProvider>
        <admin-layout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-black lg:p-6"
            >
                <div class="p-6 space-y-6">
                  
                    <!-- Permissions globales -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Permissions Globales</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <h3 class="font-semibold mb-2">Système</h3>
                            <div class="flex flex-wrap gap-2 mb-4">
                                <Badge
                                    v-for="perm in systemPermissions"
                                    :key="perm.id"
                                    variant="destructive"
                                >
                                    {{ perm.name }}
                                </Badge>
                            </div>

                            <h3 class="font-semibold mb-2">Organisation</h3>
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="perm in orgPermissions"
                                    :key="perm.id"
                                    variant="success"
                                >
                                    {{ perm.name }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </admin-layout>
    </SidebarProvider>
</template>
