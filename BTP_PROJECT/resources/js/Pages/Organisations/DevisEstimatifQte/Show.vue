<script setup lang="ts">
import { computed,ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ArrowLeft, Edit, Trash2, Building2 } from "lucide-vue-next";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import { Button } from "@/components/ui/button";
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

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs";


const isOpen = ref(false);
const openModal = () => {
    isOpen.value = true;
};
const closeModal = () => {
    isOpen.value = false;
};
const confirmDelete = () => {
    router.delete(
        route("batiments.devis-estimatif-quantitatif.destroy", {
            batiment: props.batiment.id,
            devis_estimatif_quantitatif: props.devis,
        }),
        {
            onFinish: () => {
                closeModal();
            },
        }
    );
};
const props = defineProps<{
    batiment: {
        id: number;
        nom: string;
    };
    devis: {
        id: number;
        intitule: string;
        statut: string;
    };
    corpsEtats: any[];
    devise: string;
}>();

/* ===== CALCULS ===== */
const totalLot = (lot: any) =>
    lot.composants.reduce(
        (sum: number, c: any) => sum + c.quantite * c.prix_unitaire,
        0
    );

const totalCorpsEtat = (ce: any) =>
    ce.lots.reduce((sum: number, lot: any) => sum + totalLot(lot), 0);

const totalGeneral = computed(() =>
    props.corpsEtats.reduce(
        (sum: number, ce: any) => sum + totalCorpsEtat(ce),
        0
    )
);



const valider = () => {
    router.post(
        route("batiments.devis-estimatif-quantitatif.valider", {
            batiment: props.batiment.id,
            devis_estimatif_quantitatif: props.devis.id,
        })
    );
};

const brouillon = () => {
    router.post(
        route("batiments.devis-estimatif-quantitatif.brouillon", {
            batiment: props.batiment.id,
            devis_estimatif_quantitatif: props.devis.id,
        })
    );
};
</script>

<template>
    <Head title="Détail du devis quantitatif" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb
                :pageTitle="`Devis quantitatif – ${batiment.nom}`"
            />

            <Card class="m-6">
                <CardHeader>
                    <CardTitle>
                        {{ devis.intitule }}
                        <span class="ml-2 text-sm text-muted-foreground">
                            ({{ devis.statut }})
                        </span>
                        

                        <Link
                            :href="
                                route(
                                    'batiments.devis-estimatif-quantitatif.edit',
                                    {
                                        batiment: batiment.id,
                                        devis_estimatif_quantitatif: 1,
                                    }
                                )
                            "
                            class="inline-flex items-center px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-md transition-colors"
                        >
                            <Edit class="w-4 h-4 mr-1" />
                            Modifier
                        </Link>

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
            
                    </CardTitle>
                </CardHeader>

                <CardContent>
                    <Tabs :default-value="corpsEtats[0]?.id.toString()">
                        <TabsList class="mb-4">
                            <TabsTrigger
                                v-for="ce in corpsEtats"
                                :key="ce.id"
                                :value="ce.id.toString()"
                            >
                                {{ ce.intitule }}
                            </TabsTrigger>
                        </TabsList>

                        <TabsContent
                            v-for="ce in corpsEtats"
                            :key="ce.id"
                            :value="ce.id.toString()"
                        >
                            <div class="space-y-6">
                                <div
                                    v-for="(lot, lotIndex) in ce.lots"
                                    :key="lotIndex"
                                    class="border rounded-lg p-4 space-y-4"
                                >
                                    <!-- LOT HEADER -->
                                    <div class="flex justify-between">
                                        <div>
                                            <div class="font-semibold">
                                                {{ lot.code }} —
                                                {{ lot.intitule }}
                                            </div>
                                        </div>
                                        <div class="font-semibold text-primary">
                                            {{ totalLot(lot).toLocaleString() }}
                                            {{ devise }}
                                        </div>
                                    </div>

                                    <!-- TABLE COMPOSANTS -->
                                    <table
                                        class="w-full text-sm border-separate border-spacing-y-2 [&_th]:px-4 [&_td]:px-4"
                                    >
                                        <thead>
                                            <tr class="text-left">
                                                <th>Désignation</th>
                                                <th>Unité</th>
                                                <th>Qté</th>
                                                <th>PU</th>
                                                <th>Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(c, i) in lot.composants"
                                                :key="i"
                                            >
                                                <td>{{ c.designation }}</td>
                                                <td>
                                                    {{ c.unite?.code ?? "-" }}
                                                </td>
                                                <td>{{ c.quantite }}</td>
                                                <td>
                                                    {{
                                                        c.prix_unitaire.toLocaleString()
                                                    }}
                                                </td>
                                                <td class="font-medium">
                                                    {{
                                                        (
                                                            c.quantite *
                                                            c.prix_unitaire
                                                        ).toLocaleString()
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- TOTAL CORPS D'ÉTAT -->
                                <div class="flex justify-end">
                                    <div
                                        class="bg-gray-100 rounded-lg px-4 py-2 font-bold"
                                    >
                                        Total {{ ce.intitule }} :
                                        <span class="ml-2 text-primary">
                                            {{
                                                totalCorpsEtat(
                                                    ce
                                                ).toLocaleString()
                                            }}
                                            {{ devise }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </TabsContent>
                    </Tabs>

                    <!-- TOTAL GÉNÉRAL -->
                    <div
                        class="flex justify-between items-center mt-8 text-lg font-bold"
                    >
                        <span>Total général</span>
                        <span class="text-primary">
                            {{ totalGeneral.toLocaleString() }} {{ devise }}
                        </span>
                    </div>

                    <div
                        class="flex flex-wrap items-center justify-end gap-3 mt-6"
                    >
                        <!-- Modifier -->
                        <Link
                            :href="
                                route(
                                    'batiments.devis-estimatif-quantitatif.editCorpsEtat',
                                    {
                                        batiment: props.batiment.id,
                                        devis_estimatif_quantitatif:
                                            props.devis.id,
                                    }
                                )
                            "
                            class="inline-flex items-center px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-md transition-colors"
                        >
                            <Edit class="w-4 h-4 mr-1" />
                            Modifier
                        </Link>

                        <!-- Supprimer -->
                       <Button variant="destructive" class="" @click="openModal">
                    <TrashIcon class="w-4 h-4 mr-1" />
                    Supprimer
                </Button>
                    </div>

                      <!-- Modal de suppression -->
                                <AlertDialog v-model:open="isOpen">
                                    <AlertDialogTrigger as-child> </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Confirmer la suppression</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Voulez-vous vraiment supprimer le ce devis Estimatif
                                                <strong>{{ devis.intitule }}</strong> ? Cette action
                                                est irréversible.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                                            <AlertDialogAction @click.prevent="confirmDelete">Continue</AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                </CardContent>
            </Card>
        </AdminLayout>
    </SidebarProvider>
</template>
