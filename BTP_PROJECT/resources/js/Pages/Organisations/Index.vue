<script setup lang="ts">
import { Head, router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import { Switch } from "@/components/ui/switch";

import {
    AlertDialog,
    AlertDialogTrigger,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogCancel,
    AlertDialogAction,
} from "@/components/ui/alert-dialog";
import {
    Table,
    TableHeader,
    TableRow,
    TableHead,
    TableBody,
    TableCell,
} from "@/components/ui/table";

import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

import { Eye, UserPlus, Trash2, CheckCircle, Circle } from "lucide-vue-next";

defineProps<{
    organisations?: Array<any>;
    teamsWithRoles?: Array<{
        team: any;
        roles: string[];
        statut: boolean;
    }>;
}>();
const actionType = ref<"activate" | "deactivate">("activate");
const openConfirm = ref(false);
const selectedOrg = ref<any | null>(null);
const page = usePage();
const session: any = page.props.session;
const activeOrganisationId = ref(session.active_organisation?.id || null);

function askActivation(org) {
    selectedOrg.value = org.id;

    // si on clique sur l’org déjà active → désactivation
    actionType.value =
        org.id === activeOrganisationId.value ? "deactivate" : "activate";
    openConfirm.value = true;
}
function confirmActivation() {
    if (!selectedOrg.value) return;

    if (actionType.value === "activate") {
        router.post(
            route("organisations.activate", selectedOrg.value),
            {},
            { preserveScroll: true }
        );
    } else {
        alert("deactivate");

        router.post(
            route("organisations.deactivate", selectedOrg.value),
            {},
            { preserveScroll: true }
        );
    }

    openConfirm.value = false;
}
</script>

<template>
    <Head title="Organisations" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb pageTitle="Organisations" />
            <div class="rounded-xl border bg-background">
              {{ page.props.flash }}
                            {{ page.props.session }}

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Organisation</TableHead>
                            <TableHead>Rôles</TableHead>
                            <TableHead>Statut</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <!-- ================= SUPER ADMIN ================= -->
                        <template v-if="organisations">
                            <TableRow
                                v-for="org in organisations"
                                :key="org.id"
                            >
                                <TableCell class="font-medium">
                                    {{ org.nom }}
                                </TableCell>

                                <TableCell>
                                    <Badge variant="outline">—</Badge>
                                </TableCell>

                                <TableCell>
                                    <Badge variant="secondary"> Système </Badge>
                                </TableCell>

                                <TableCell class="text-right">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="
                                            router.visit(
                                                route(
                                                    'organisations.show',
                                                    org.id
                                                )
                                            )
                                        "
                                    >
                                        <Eye class="w-4 h-4 mr-2" />
                                        Voir
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </template>

                        <!-- ================= UTILISATEUR NORMAL ================= -->
                        <template v-else-if="teamsWithRoles">
                            <TableRow
                                v-for="item in teamsWithRoles"
                                :key="item.team.id"
                                :class="!item.statut && 'opacity-50'"
                            >
                                <!-- Organisation -->
                                <TableCell class="font-medium">
                                    {{ item.team.nom }}
                                </TableCell>

                                <!-- Rôles -->
                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="role in item.roles"
                                            :key="role"
                                            variant="secondary"
                                        >
                                            {{ role }}
                                        </Badge>
                                    </div>
                                </TableCell>

                                <!-- Statut -->
                                <!-- Statut -->
                                 {{ page.props.flash }}
                                <TableCell>
                                    <div class="flex items-center gap-3">
                                        <Switch
                                            :checked="
                                                item.team.id ===
                                                activeOrganisationId
                                            "
                                            @click="askActivation(item.team)"
                                        />
                                        <Badge
                                            :variant="
                                                item.team.id ===
                                                activeOrganisationId
                                                    ? 'default'
                                                    : 'outline'
                                            "
                                        >
                                            {{
                                                item.team.id ===
                                                activeOrganisationId
                                                    ? "Active"
                                                    : "Inactive"
                                            }}
                                        </Badge>
                                    </div>
                                </TableCell>

                                <!-- Actions -->
                                <TableCell class="text-right space-x-2">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        :disabled="!item.statut"
                                        @click="
                                            router.visit(
                                                route(
                                                    'organisations.addUser',
                                                    item.team.id
                                                )
                                            )
                                        "
                                    >
                                        <UserPlus class="w-4 h-4 mr-1" />
                                        Ajouter
                                    </Button>

                                    <Button
                                        v-if="item.roles.includes('ORG_ADMIN')"
                                        size="sm"
                                        variant="destructive"
                                        :disabled="!item.statut"
                                        @click="
                                            router.visit(
                                                route(
                                                    'organisations.users.index',
                                                    item.team.id
                                                )
                                            )
                                        "
                                    >
                                        <Trash2 class="w-4 h-4 mr-1" />
                                        Supprimer
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </template>

                        <!-- ================= EMPTY ================= -->
                        <TableRow v-else>
                            <TableCell
                                colspan="4"
                                class="text-center text-muted-foreground py-10"
                            >
                                Aucune organisation trouvée
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <AlertDialog v-model:open="openConfirm">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Changer d’organisation active ?
                        </AlertDialogTitle>

                        <AlertDialogDescription>
                            Vous êtes sur le point d’activer l’organisation
                            <strong>{{ selectedOrg?.nom }}</strong
                            >.

                            <br /><br />
                            Toutes les actions suivantes seront effectuées dans
                            cette organisation.
                        </AlertDialogDescription>
                    </AlertDialogHeader>

                    <AlertDialogFooter>
                        <AlertDialogCancel> Annuler </AlertDialogCancel>

                        <AlertDialogAction
                            class="bg-primary text-white"
                            @click="confirmActivation"
                        >
                            Confirmer
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AdminLayout>
    </SidebarProvider>
</template>
