<script setup>
import { computed, ref } from "vue";
import { router, Head } from "@inertiajs/vue3";
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogCancel,
    AlertDialogAction,
} from "@/components/ui/alert-dialog";

import { TrashIcon, Copy, Pencil, Printer, CheckCircle, RotateCcw } from "lucide-vue-next";

import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from "@/components/ui/accordion";

import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

const props = defineProps({
    template: { type: Object, required: true },
    totauxParNiveau: { type: Array, required: true },
    totalGeneral: { type: Number, required: true },
    devise: { type: String, default: "MAD" },
    permissions: { type: Object, required: true },
});

/**
 * Groupement des composants par niveau
 */
const composantsParNiveau = computed(() => {
    const map = {};
    props.template.composants.forEach((comp) => {
        if (!map[comp.niveau.id]) {
            map[comp.niveau.id] = { niveau: comp.niveau, composants: [] };
        }
        map[comp.niveau.id].composants.push(comp);
    });
    return Object.values(map);
});

/**
 * Permissions backend
 */
const canEdit = computed(() => props.permissions.canEdit);
const canDelete = computed(() => props.permissions.canDelete);
const canCreate = computed(() => props.permissions.canCreate);
const canValidate = computed(() => props.permissions.canValidate);

/**
 * Actions
 */
const editTemplate = () => {
    router.visit(route("batiments.devisestimatif.edit", {
        batiment: props.template.batiment.id,
        devisestimatif: props.template.id,
    }));
};

const reuseTemplate = () => {
    router.visit(route("templates.reuse", { template: props.template.id }));
};

const printTemplate = () => {
    window.open(route("devisestimatif.pdf", {
        batiment: props.template.batiment.id,
        devis: props.template.id,
    }), "_blank");
};

const validateTemplate = () => {
    router.post(route("templates.validate", { template: props.template.id }));
};

const unvalidateTemplate = () => {
    router.post(route("templates.unvalidate", { template: props.template.id }));
};

/**
 * Suppression
 */
const isOpen = ref(false);
const openModal = () => (isOpen.value = true);
const closeModal = () => (isOpen.value = false);

const confirmDelete = () => {
    router.delete(route("batiments.devisestimatif.destroy", {
        batiment: props.template.batiment.id,
        devisestimatif: props.template.id,
    }), {
        onFinish: closeModal,
    });
};
</script>


<template>
    <SidebarProvider>
        <AdminLayout>

            <Head :title="`Template – ${template.intitule}`" />
            <PageBreadcrumb pageTitle="Détail du template de devis" />

            <!-- ACTIONS -->
            <div class="mt-6 flex justify-end gap-3 my-4">

                <!-- Modifier -->
                <Button v-if="canEdit && template.statut !== 'valide'" variant="secondary" @click="editTemplate">
                    <Pencil class="w-4 h-4 mr-1" />
                    Modifier
                </Button>

                <!-- Valider -->
                <Button v-if="canValidate && template.statut === 'brouillon'" variant="default"
                    @click="validateTemplate">
                    <CheckCircle class="w-4 h-4 mr-1" />
                    Valider
                </Button>

                <!-- Revenir brouillon -->
                <Button v-if="canValidate && template.statut === 'valide'" variant="outline"
                    @click="unvalidateTemplate">
                    <RotateCcw class="w-4 h-4 mr-1" />
                    Revenir brouillon
                </Button>

                <!-- Imprimer -->
                <Button variant="outline" @click="printTemplate">
                    <Printer class="w-4 h-4 mr-1" />
                    Imprimer
                </Button>

                <!-- Réutiliser -->
                <Button v-if="canCreate" variant="outline" @click="reuseTemplate">
                    <Copy class="w-4 h-4 mr-1" />
                    Réutiliser
                </Button>

                <!-- Supprimer -->
                <Button v-if="canDelete" variant="outline" class="text-red-600 hover:bg-red-50" @click="openModal">
                    <TrashIcon class="w-4 h-4 mr-1" />
                    Supprimer
                </Button>
            </div>

            <!-- INFOS -->
          

            <!-- NIVEAUX + TABLEAUX -->
            <!-- (inchangé – ton code est correct ici) -->
            <Card class="mb-6">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>{{ template.intitule }}</CardTitle>
                        <Badge :variant="template.statut === 'valide' ? 'default' : 'secondary'">
                            {{ template.statut }}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <strong class="text-gray-600">Code</strong><br />
                        <span class="font-mono font-semibold text-blue-600">{{ template.code }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-600">Organisation</strong><br />
                        {{ template.batiment.projet.organisation.nom }}
                        <Badge v-if="template.batiment.projet.organisation.is_system" variant="outline" class="ml-2">
                            Système</Badge>
                    </div>
                    <div>
                        <strong class="text-gray-600">Projet</strong><br />
                        {{ template.batiment.projet.nom }}
                    </div>
                    <div>
                        <strong class="text-gray-600">Bâtiment</strong><br />
                        {{ template.batiment.nom }}
                    </div>
                    <div>
                        <strong class="text-gray-600">Date de création</strong><br />
                        {{ template.created_at }}
                    </div>
                    <div>
                        <strong class="text-gray-600">Nombre de composants</strong><br />
                        <Badge variant="secondary">{{ template.composants.length }}</Badge>
                    </div>
                </CardContent>
            </Card>

            <!-- NIVEAUX -->
            <Accordion type="multiple" class="space-y-4">
                <AccordionItem v-for="bloc in composantsParNiveau" :key="bloc.niveau.id"
                    :value="`niveau-${bloc.niveau.id}`">
                    <AccordionTrigger
                        class="px-4 py-3 bg-gray-50 dark:bg-gray-900 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800">
                        <div class="flex justify-between w-full items-center">
                            <span class="font-semibold text-lg">{{ bloc.niveau.nom }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Total : {{
                                    totauxParNiveau.find(t => t.niveau_id === bloc.niveau.id)?.total.toLocaleString()
                                }} {{ devise }}
                            </span>
                        </div>
                    </AccordionTrigger>

                    <AccordionContent>
                        <Card class="mt-3">
                            <CardContent class="overflow-x-auto p-0">
                                <table class="w-full text-sm border border-gray-200 dark:border-gray-800">
                                    <thead class="bg-gray-100 dark:bg-gray-900">
                                        <tr>
                                            <th class="border p-2 text-left">Code</th>
                                            <th class="border p-2 text-left">Pièce</th>
                                            <th class="border p-2 text-left">Unité</th>
                                            <th class="border p-2 text-right">Qté</th>
                                            <th class="border p-2 text-right">Prix unitaire</th>
                                            <th class="border p-2 text-right">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="comp in bloc.composants" :key="comp.id"
                                            class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                            <td class="border p-2 font-mono text-xs">{{ comp.code }}</td>
                                            <td class="border p-2">{{ comp.piece }}</td>
                                            <td class="border p-2">{{ comp.unite.nom }}</td>
                                            <td class="border p-2 text-right">{{ comp.qte }}</td>
                                            <td class="border p-2 text-right">{{ comp.prix_unitaire.toLocaleString() }}
                                            </td>
                                            <td class="border p-2 text-right font-semibold">{{
                                                comp.montant.toLocaleString() }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-gray-50 dark:bg-gray-900 font-bold">
                                            <td colspan="5" class="border p-2 text-right">Total {{ bloc.niveau.nom }}
                                            </td>
                                            <td class="border p-2 text-right">{{totauxParNiveau.find(t => t.niveau_id
                                                === bloc.niveau.id)?.total.toLocaleString()}}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </CardContent>
                        </Card>
                    </AccordionContent>
                </AccordionItem>
            </Accordion>

            <!-- TOTAL GÉNÉRAL -->
            <Card class="mt-6 bg-blue-50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-800">
                <CardContent class="flex justify-between items-center py-6">
                    <span class="text-xl font-bold text-blue-900 dark:text-blue-100">Total général</span>
                    <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ totalGeneral.toLocaleString()
                    }} {{ devise }}</span>
                </CardContent>
            </Card>
            <!-- MODAL SUPPRESSION -->
            <AlertDialog v-model:open="isOpen">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Confirmer la suppression</AlertDialogTitle>
                        <AlertDialogDescription>
                            Supprimer définitivement
                            <strong>{{ template.code }} – {{ template.intitule }}</strong> ?
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel @click="closeModal">Annuler</AlertDialogCancel>
                        <AlertDialogAction class="bg-red-600" @click="confirmDelete">
                            Supprimer
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

        </AdminLayout>
    </SidebarProvider>
</template>
