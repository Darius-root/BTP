<script setup>
import AdminLayout from "@/components/layout/AdminLayout.vue";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    CardAction,
} from "@/components/ui/card";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs";
import { TrashIcon, PenBoxIcon, EyeIcon, PlusIcon } from "lucide-vue-next";
import {
    Table,
    TableHeader,
    TableRow,
    TableHead,
    TableCell,
    TableBody,
} from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { router,Head , usePage} from "@inertiajs/vue3";
import { ref } from "vue";
const page = usePage();

const props = defineProps({
    systemRoles: Array,
    orgRoles: Array,
});

const currentPageTitle = ref("Gestion des rôles");

const goToCreate = (type) => {
    router.visit(route("roles.create", { type }));
};

const viewRole = (role) => {
    router.visit(route("roles.show", role.id));
};

const editRole = (role) => {
    router.visit(route("roles.edit", role.id));
};

const deleteRole = (role) => {
    if (confirm(`Supprimer le rôle "${role.name}" ?`)) {
        router.delete(route("roles.destroy", role.id));
    }
};
</script>

<template>
    <Head title="Gestion des rôles" />
    <SidebarProvider>
        <admin-layout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />
           
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:p-6"
            >
                <div class="p-6 space-y-6">
                    <Tabs default-value="system" class="w-full">
                        <TabsList class="grid w-full grid-cols-2">
                            <TabsTrigger value="system"
                                >Rôles Système</TabsTrigger
                            >
                            <TabsTrigger value="organisation"
                                >Rôles Organisationnels</TabsTrigger
                            >
                        </TabsList>

                        <!-- TAB : RÔLES SYSTÈME -->
                        <TabsContent value="system">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Rôles Système</CardTitle>
                                    <CardAction>
                                        <Button variant="outline" @click="goToCreate('system')">
                                            Ajouter un rôle système
                                            <PlusIcon
                                                class="w-4 h-4 ml-2 text-blue-600"
                                            />
                                        </Button>
                                    </CardAction>
                                </CardHeader>
                                <CardContent>
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead
                                                    >Nom du rôle</TableHead
                                                >
                                                <TableHead class="text-right"
                                                    >Actions</TableHead
                                                >
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow
                                                v-for="role in systemRoles"
                                                :key="role.id"
                                            >
                                                <TableCell>{{
                                                    role.name
                                                }}</TableCell>
                                                <TableCell class="text-right">
                                                    <div
                                                        class="flex justify-end space-x-2"
                                                    >
                                                        <Button
                                                            size="sm"
                                                            variant="secondary"
                                                            class="text-green-600"
                                                            @click="
                                                                viewRole(role)
                                                            "
                                                        >
                                                            <EyeIcon
                                                                class="w-4 h-4"
                                                            />
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="secondary"
                                                            class="text-yellow-600"
                                                            @click="
                                                                editRole(role)
                                                            "
                                                        >
                                                            <PenBoxIcon
                                                                class="w-4 h-4"
                                                            />
                                                        </Button>
                                                        <!-- <Button
                                                            size="sm"
                                                            variant="secondary"
                                                            class="text-red-600"
                                                            @click="
                                                                deleteRole(role)
                                                            "
                                                        >
                                                            <TrashIcon
                                                                class="w-4 h-4"
                                                            />
                                                        </Button> -->
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <!-- TAB : RÔLES ORGANISATIONNELS -->
                        <TabsContent value="organisation">
                            <Card>
                                <CardHeader>
                                    <CardTitle
                                        >Rôles Organisationnels</CardTitle
                                    >
                                    <CardAction>
                                        <Button
                                            variant="outline"
                                            @click="goToCreate('organisation')"
                                        >
                                            Ajouter un rôle organisationnel
                                            <PlusIcon
                                                class="w-4 h-4 ml-2 text-blue-600"
                                            />
                                        </Button>
                                    </CardAction>
                                </CardHeader>
                                <CardContent>
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead
                                                    >Nom du rôle</TableHead
                                                >
                                                <TableHead class="text-right"
                                                    >Actions</TableHead
                                                >
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow
                                                v-for="role in orgRoles"
                                                :key="role.id"
                                            >
                                                <TableCell>{{
                                                    role.name
                                                }}</TableCell>
                                                <TableCell class="text-right">
                                                    <div
                                                        class="flex justify-end space-x-2"
                                                    >
                                                        <Button
                                                            size="sm"
                                                            variant="secondary"
                                                            @click="
                                                                viewRole(role)
                                                            "
                                                        >
                                                            <EyeIcon
                                                                class="w-4 h-4 text-green-600"
                                                            />
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="secondary"
                                                            @click="
                                                                editRole(role)
                                                            "
                                                        >
                                                            <PenBoxIcon
                                                                class="w-4 h-4 text-yellow-600"
                                                            />
                                                        </Button>
                                                        <!-- <Button
                                                            size="sm"
                                                            variant="secondary"
                                                            @click="
                                                                deleteRole(role)
                                                            "
                                                        >
                                                            <TrashIcon
                                                                class="w-4 h-4 text-red-600"
                                                            />
                                                        </Button> -->
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </CardContent>
                            </Card>
                        </TabsContent>
                    </Tabs>
                </div>
            </div>
        </admin-layout>
    </SidebarProvider>
</template>
