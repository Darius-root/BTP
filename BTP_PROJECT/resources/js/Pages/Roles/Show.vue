<script setup>
import AdminLayout from "@/components/layout/AdminLayout.vue";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { router, Head  } from "@inertiajs/vue3";
import { ArrowLeft } from "lucide-vue-next";
import { ref } from "vue";
import { TrashIcon, PenBoxIcon } from "lucide-vue-next";
const props = defineProps({
    role: Object,
});

const isOpen = ref(false);
const confirmDelete = () => {
    router.delete(route("roles.destroy", props.role.id), {
        onFinish: () => {
            closeModal();
        },
    });
};

const openModal = () => {
    isOpen.value = true;
};
const closeModal = () => {
    isOpen.value = false;
};

const goBack = () => {
    router.visit(route("roles.index"));
};

const editRole = (role) => {
    router.visit(route("roles.edit", props.role.id));
};
</script>

<template>
    <Head title="Rôle app " />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="`Détails du rôle`" />

            <div class="rounded-2xl border bg-white p-6 dark:bg-black">
                <div class="mb-4">
                    <Button variant="outline" @click="goBack">
                        <arrowLeft class="w-4 h-4 ml-2 mb" />
                        Retour
                    </Button>
                </div>
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between">
                            <span>{{ role.name }}</span>

                            <Badge
                                :variant="
                                    role.name.startsWith('SYSTEM_')
                                        ? 'destructive'
                                        : 'secondary'
                                "
                            >
                                {{
                                    role.name.startsWith("SYSTEM_")
                                        ? "Rôle Système"
                                        : "Rôle Organisationnel"
                                }}
                            </Badge>
                        </CardTitle>
                    </CardHeader>

                    <CardContent class="space-y-6">
                        <!-- PERMISSIONS -->
                        <div>
                            <h3 class="mb-3 font-semibold">
                                Permissions associées
                            </h3>

                            <div
                                v-if="role.permissions.length"
                                class="flex flex-wrap gap-2"
                            >
                                <Badge
                                    v-for="permission in role.permissions"
                                    :key="permission.id"
                                    variant="outline"
                                >
                                    {{ permission.name }}
                                </Badge>
                            </div>

                            <p v-else class="text-sm text-gray-500 italic">
                                Aucune permission associée à ce rôle.
                            </p>
                        </div>

                        <!-- ACTIONS -->
                        <div class="flex justify-end space-x-3">
                            <Button
                                variant="outline"
                                class=""
                                @click="editRole(role)"
                                ><PenBoxIcon
                                    class="w-4 h-4 mr-1 text-yellow-600"
                                />
                                Modifier
                            </Button>
                            <Button
                                variant="outline"
                                class=""
                                @click="openModal"
                            >
                                <TrashIcon class="w-4 h-4 mr-1 text-red-600" />
                                Supprimer
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Modal de suppression -->
            <AlertDialog v-model:open="isOpen">
                <AlertDialogTrigger as-child> </AlertDialogTrigger>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle
                            >Confirmer la suppression</AlertDialogTitle
                        >
                        <AlertDialogDescription>
                            Voulez-vous vraiment supprimer le rôle
                            <strong>{{ role.name }}</strong> ? Cette action est
                            irréversible.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction @click.prevent="confirmDelete"
                            >Continue</AlertDialogAction
                        >
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AdminLayout>
    </SidebarProvider>
</template>
