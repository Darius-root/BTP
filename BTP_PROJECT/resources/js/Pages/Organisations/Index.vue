<script setup lang="ts">
import { Head, router, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
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
import { Eye, UserPlus, Trash2 } from "lucide-vue-next";
import { refAutoReset } from "@vueuse/core";

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
// stocke l'objet organisation complet (pas seulement l'id)
const selectedOrg = ref<any | null>(null);

const page = usePage();

const activeOrganisationId = computed(
    // @ts-ignore
    () => page.props.session.active_organisation?.id || null
);

function askActivation(org: any) {
    // stocke l'objet (pour affichage du nom etc.)
    selectedOrg.value = org;

    // si on clique sur l’org déjà active → désactivation
    actionType.value =
        org.id === activeOrganisationId.value ? "deactivate" : "activate";
    openConfirm.value = true;
}

function confirmActivation() {
    if (!selectedOrg.value) return;

    const id = selectedOrg.value.id;

    if (actionType.value === "activate") {
        router.post(
            route("organisations.activate", id),
            {},
            {
                preserveScroll: false,

                onSuccess: () => {
                    router.reload();
                },
            }
        );
    } else {
        router.post(
            route("organisations.deactivate", id),
            {},
            {
                preserveScroll: false,

                onSuccess: () => {
                    router.reload();
                },
            }
        );
    }

    // fermer le dialog — l'état réel (activeOrganisation) viendra du serveur / Inertia après la requête
    openConfirm.value = false;
    // on peut aussi réinitialiser selectedOrg si on veut :
    selectedOrg.value = null;
}
</script>

<template>
    <Head title="Organisations" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb pageTitle="Organisations" />
            <div class="rounded-xl border bg-background">
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
                                <TableCell class="font-medium">{{
                                    org.nom
                                }}</TableCell>
                                <TableCell
                                    ><Badge variant="outline"
                                        >—</Badge
                                    ></TableCell
                                >
                                <TableCell
                                    ><Badge variant="secondary">
                                        Système
                                    </Badge></TableCell
                                >
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
                                :class="
                                    item.team.id !== activeOrganisationId &&
                                    'opacity-50'
                                "
                            >
                                <TableCell class="font-medium">{{
                                    item.team.nom
                                }}</TableCell>

                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="role in item.roles"
                                            :key="role"
                                            variant="secondary"
                                            >{{ role }}</Badge
                                        >
                                    </div>
                                </TableCell>

                                <TableCell>
                                    <div class="flex items-center gap-3">
                                        <Switch
                                            :modelValue="
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

                                <TableCell class="text-right space-x-2">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        :disabled="
                                            item.team.id !==
                                            activeOrganisationId
                                        "
                                        @click="
                                            router.visit(
                                                route(
                                                    'organisations.users.create',
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
                                        :variant="
                                            item.team.id ===
                                            activeOrganisationId
                                                ? 'destructive'
                                                : 'outline'
                                        "
                                        :disabled="
                                            item.team.id !==
                                            activeOrganisationId
                                        "
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
                            <!-- titre dynamique selon action -->
                            {{
                                actionType === "activate"
                                    ? "Activer cette organisation ?"
                                    : "Désactiver cette organisation ?"
                            }}
                        </AlertDialogTitle>

                        <AlertDialogDescription>
                            <template v-if="actionType === 'activate'">
                                Vous êtes sur le point d’activer l’organisation
                                <strong>{{
                                    selectedOrg ? selectedOrg.nom : ""
                                }}</strong
                                >. <br /><br />
                                Toutes les actions suivantes seront effectuées
                                dans cette organisation.
                            </template>
                            <template v-else>
                                Vous êtes sur le point de désactiver
                                l’organisation
                                <strong>{{
                                    selectedOrg ? selectedOrg.nom : ""
                                }}</strong
                                >. <br /><br />
                                Après désactivation, vous ne pourrez plus
                                effectuer d’actions dans cette organisation tant
                                qu’elle ne sera pas réactivée.
                            </template>
                        </AlertDialogDescription>
                    </AlertDialogHeader>

                    <AlertDialogFooter>
                        <AlertDialogCancel> Annuler </AlertDialogCancel>

                        <AlertDialogAction
                            :class="
                                actionType === 'activate'
                                    ? 'bg-primary text-white'
                                    : 'bg-red-600 text-white'
                            "
                            @click="confirmActivation"
                        >
                            {{
                                actionType === "activate"
                                    ? "Activer"
                                    : "Désactiver"
                            }}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </AdminLayout>
    </SidebarProvider>
</template>
