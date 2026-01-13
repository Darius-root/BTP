<script setup>
import { computed, ref } from "vue";
import { router, Head, usePage } from "@inertiajs/vue3";
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

import { TrashIcon } from "lucide-vue-next";

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

const props = defineProps({
    batiment: Object,
    devis: Object,
    totauxParNiveau: Array,
    totalGeneral: Number,
    devise: String,
});

/**
 * Regrouper les composants par niveau
 */
const composantsParNiveau = computed(() => {
    const map = {};

    props.devis.composants.forEach((comp) => {
        if (!map[comp.niveau.id]) {
            map[comp.niveau.id] = {
                niveau: comp.niveau,
                composants: [],
            };
        }

        map[comp.niveau.id].composants.push(comp);
    });

    return Object.values(map);
});

const editDevis = () => {
    router.visit(
        route("batiments.devisestimatif.edit", {
            batiment: props.batiment.id,
            devisestimatif: props.devis,
        })
    );
};
const isOpen = ref(false);
const openModal = () => {
    isOpen.value = true;
};
const closeModal = () => {
    isOpen.value = false;
};
const confirmDelete = () => {
    router.delete(
        route("batiments.devisestimatif.destroy", {
            batiment: props.batiment.id,
            devisestimatif: props.devis,
        }),
        {
            onFinish: () => {
                closeModal();
            },
        }
    );
};
const valider = () => {
    router.post(
        route("devisestimatif.valider", {
            batiment: props.batiment.id,
            devis: props.devis,
        })
    );
};

const brouillon = () => {
    router.post(
        route("devisestimatif.brouillon", {
            batiment: props.batiment.id,
            devis: props.devis,
        })
    );
};
</script>
<template>
    <SidebarProvider>
        <AdminLayout>
            <Head :title="`Devis estimatif – ${devis.intitule}`" />

            <PageBreadcrumb :pageTitle="'Détail du devis estimatif'" />
            <!-- ACTIONS -->
            <div class="mt-6 flex justify-end gap-3 my-4">
                <Button
                    @click.prevent="editDevis()"
                    :v-if="devis.statut !== 'valide'"
                    variant="secondary"
                    >Modifier
                </Button>

              
                <Button variant="outline" class="" @click="openModal">
                    <TrashIcon class="w-4 h-4 mr-1 text-red-600" />
                    Supprimer
                </Button>

                <div class="flex gap-2">
                    <!-- Bouton VALIDER -->
                    <Button
                        v-if="devis.statut === 'brouillon'"
                        class="bg-green-600 hover:bg-green-700"
                        @click="valider"
                    >
                        Valider le devis
                    </Button>

                    <!-- Bouton BROUILLON -->
                    <Button
                        v-if="devis.statut === 'valide'"
                        variant="destructive"
                        @click="brouillon"
                    >
                        Repasser en brouillon
                    </Button>
                </div>
            </div>

            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>{{ devis.intitule }}</CardTitle>
                </CardHeader>
                <CardContent
                    class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm"
                >
                    <div>
                        <strong>Organisation</strong><br />
                        {{ devis.batiment.projet.organisation.nom }}
                    </div>
                    <div>
                        <strong>Projet</strong><br />
                        {{ devis.batiment.projet.nom }}
                    </div>
                    <div>
                        <strong>Bâtiment</strong><br />
                        {{ devis.batiment.nom }}
                    </div>
                    <div>
                        <strong>Statut</strong><br />
                        <span
                            :class="[
                                'px-2 py-1 rounded text-xs font-semibold',
                                devis.statut === 'valide'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-yellow-100 text-yellow-700',
                            ]"
                        >
                            {{ devis.statut }}
                        </span>
                    </div>
                </CardContent>
            </Card>

            <!-- NIVEAUX -->
            <Accordion type="multiple" class="space-y-4">
                <AccordionItem
                    v-for="bloc in composantsParNiveau"
                    :key="bloc.niveau.id"
                    :value="`niveau-${bloc.niveau.id}`"
                >
                    <!-- HEADER -->
                    <AccordionTrigger class="px-4 py-3 bg-gray-50 rounded-xl">
                        <div class="flex justify-between w-full items-center">
                            <span class="font-semibold text-lg">
                                {{ bloc.niveau.nom }}
                            </span>
                            <span class="text-sm text-gray-500">
                                Total :
                                {{
                                    totauxParNiveau
                                        .find(
                                            (t) =>
                                                t.niveau_id === bloc.niveau.id
                                        )
                                        ?.total.toLocaleString()
                                }}
                                {{devise}}
                            </span>
                        </div>
                    </AccordionTrigger>

                    <!-- CONTENT -->
                    <AccordionContent>
                        <Card class="mt-3">
                            <CardContent class="overflow-x-auto p-0">
                                <table
                                    class="w-full text-sm border border-gray-200"
                                >
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="border p-2">Code</th>
                                            <th class="border p-2">Pièce</th>
                                            <th class="border p-2">Unité</th>
                                            <th class="border p-2 text-right">
                                                Qté
                                            </th>
                                            <th class="border p-2 text-right">
                                                Prix unitaire
                                            </th>
                                            <th class="border p-2 text-right">
                                                Montant
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr
                                            v-for="comp in bloc.composants"
                                            :key="comp.id"
                                            class="hover:bg-gray-50"
                                        >
                                            <td class="border p-2">
                                                {{ comp.code }}
                                            </td>
                                            <td class="border p-2">
                                                {{ comp.piece }}
                                            </td>
                                            <td class="border p-2">
                                                {{ comp.unite.nom }}
                                            </td>
                                            <td class="border p-2 text-right">
                                                {{ comp.qte }}
                                            </td>
                                            <td class="border p-2 text-right">
                                                {{
                                                    comp.prix_unitaire.toLocaleString()
                                                }}
                                            </td>
                                            <td
                                                class="border p-2 text-right font-semibold"
                                            >
                                                {{
                                                    comp.montant.toLocaleString()
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>

                                    <tfoot>
                                        <tr class="bg-gray-50 font-bold">
                                            <td
                                                colspan="5"
                                                class="border p-2 text-right"
                                            >
                                                Total {{ bloc.niveau.nom }}
                                            </td>
                                            <td class="border p-2 text-right">
                                                {{
                                                    totauxParNiveau
                                                        .find(
                                                            (t) =>
                                                                t.niveau_id ===
                                                                bloc.niveau.id
                                                        )
                                                        ?.total.toLocaleString()
                                                }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </CardContent>
                        </Card>
                    </AccordionContent>
                </AccordionItem>
            </Accordion>

            <!-- TOTAL GÉNÉRAL -->
            <Card>
                <CardContent
                    class="flex justify-between items-center text-xl font-bold"
                >
                    <span>Total général</span>
                    <span>{{ totalGeneral.toLocaleString() }} {{devise}}</span>
                </CardContent>
            </Card>
            <!-- Modal de suppression -->
            <AlertDialog v-model:open="isOpen">
                <AlertDialogTrigger as-child> </AlertDialogTrigger>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle
                            >Confirmer la suppression</AlertDialogTitle
                        >
                        <AlertDialogDescription>
                            Voulez-vous vraiment supprimer le ce devis Estimatif
                            <strong>{{ devis.intitule }}</strong> ? Cette action
                            est irréversible.
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
