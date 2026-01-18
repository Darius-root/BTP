<script setup lang="ts">
import { computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { Edit, Trash2, Copy } from "lucide-vue-next";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import { Button } from "@/components/ui/button";
import { usePermissions } from '@/composables/usePermissions';

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs";
import { Printer } from "lucide-vue-next";


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

const { can } = usePermissions();

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

const confirmDelete = () => {
    if (
        confirm(
            `Êtes-vous sûr de vouloir supprimer le devis "${props.devis.intitule}" ? Cette action est irréversible.`
        )
    ) {
        router.delete(
            route(
                "batiments.devis-estimatif-quantitatif.destroy",
                props.devis.id
            )
        );
    }
};

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

const downloadPdf = () => {
    window.open(
        route("batiments.devis-estimatif-quantitatif.pdf", {
            batiment: props.batiment.id,
            devis_estimatif_quantitatif: props.devis.id,
        }),
        "_blank"
    );
};


</script>

<template>

    <Head title="Détail du devis quantitatif" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="`Devis quantitatif – ${batiment.nom}`" />

            <Card class="m-6">
                <CardHeader>
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <CardTitle>
                                {{ devis.intitule }}
                                <span class="ml-2 text-sm text-muted-foreground">
                                    (<strong>{{ devis.statut }}</strong>)
                                </span>
                            </CardTitle>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <!-- Réutiliser -->
                            <Link v-if="can('ORG_DEVIS_QUANTITATIF_VIEW')" :href="route(
                                'batiments.devis-estimatif-quantitatif.reuse-form',
                                { batiment: batiment.id }
                            )"
                                class="inline-flex items-center px-4 py-2 text-sm text-green-600 hover:bg-green-50 rounded-md transition-colors">
                                <Copy class="w-4 h-4 mr-1" />
                                Réutiliser
                            </Link>

                            <!-- Modifier -->
                            <Link v-if="can('ORG_DEVIS_QUANTITATIF_EDIT')" :href="route(
                                'batiments.devis-estimatif-quantitatif.edit',
                                {
                                    batiment: batiment.id,
                                    devis_estimatif_quantitatif: devis.id,
                                }
                            )"
                                class="inline-flex items-center px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                                <Edit class="w-4 h-4 mr-1" />
                                Modifier
                            </Link>

                            <!-- Valider -->
                            <button v-if="devis.statut === 'brouillon' && can('ORG_DEVIS_QUANTITATIF_VALIDE')"
                                @click="valider"
                                class="inline-flex items-center px-4 py-2 text-sm text-green-600 hover:bg-green-50 rounded-md transition-colors">
                                <CheckCircle class="w-4 h-4 mr-1" />
                                Valider le devis
                            </button>

                            <!-- Repasser en brouillon -->
                            <button v-if="devis.statut === 'valide' && can('ORG_DEVIS_QUANTITATIF_VALIDE')"
                                @click="brouillon"
                                class="inline-flex items-center px-4 py-2 text-sm text-orange-600 hover:bg-orange-50 rounded-md transition-colors">
                                <ArrowLeftCircle class="w-4 h-4 mr-1" />
                                Repasser en brouillon
                            </button>
                            <button v-if="can('ORG_DEVIS_QUANTITATIF_VIEW')" @click="downloadPdf"
                                class="inline-flex items-center px-4 py-2 text-sm text-purple-600 hover:bg-purple-50 rounded-md transition-colors">
                                <Printer class="w-4 h-4 mr-1" />
                                Imprimer PDF
                            </button>

                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <Tabs :default-value="corpsEtats[0]?.id.toString()">
                        <TabsList class="mb-4">
                            <TabsTrigger v-for="ce in corpsEtats" :key="ce.id" :value="ce.id.toString()">
                                {{ ce.intitule }}
                            </TabsTrigger>
                        </TabsList>

                        <TabsContent v-for="ce in corpsEtats" :key="ce.id" :value="ce.id.toString()">
                            <div class="space-y-6">
                                <div v-for="(lot, lotIndex) in ce.lots" :key="lotIndex"
                                    class="border rounded-lg p-4 space-y-4">
                                    <!-- LOT HEADER -->
                                    <div class="flex justify-between">
                                        <div>
                                            <div class="font-semibold">
                                                {{ lot.code }} — {{ lot.intitule }}
                                            </div>
                                        </div>
                                        <div class="font-semibold text-primary">
                                            {{ totalLot(lot).toLocaleString() }} {{ devise }}
                                        </div>
                                    </div>

                                    <!-- TABLE COMPOSANTS -->
                                    <table
                                        class="w-full text-sm border-separate border-spacing-y-2 [&_th]:px-4 [&_td]:px-4">
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
                                            <tr v-for="(c, i) in lot.composants" :key="i">
                                                <td>{{ c.designation }}</td>
                                                <td>{{ c.unite?.code ?? "-" }}</td>
                                                <td>{{ c.quantite }}</td>
                                                <td>{{ c.prix_unitaire.toLocaleString() }}</td>
                                                <td class="font-medium">
                                                    {{ (c.quantite * c.prix_unitaire).toLocaleString() }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- TOTAL CORPS D'ÉTAT -->
                                <div class="flex justify-end">
                                    <div class="bg-gray-100 rounded-lg px-4 py-2 font-bold">
                                        Total {{ ce.intitule }} :
                                        <span class="ml-2 text-primary">
                                            {{ totalCorpsEtat(ce).toLocaleString() }} {{ devise }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </TabsContent>
                    </Tabs>

                    <!-- TOTAL GÉNÉRAL -->
                    <div class="flex justify-between items-center mt-8 text-lg font-bold">
                        <span>Total général</span>
                        <span class="text-primary">
                            {{ totalGeneral.toLocaleString() }} {{ devise }}
                        </span>
                    </div>

                    <!-- ACTIONS -->
                    <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                        <!-- Modifier -->
                        <Link v-if="can('ORG_DEVIS_QUANTITATIF_EDIT')" :href="route(
                            'batiments.devis-estimatif-quantitatif.editCorpsEtat',
                            {
                                batiment: props.batiment.id,
                                devis_estimatif_quantitatif: props.devis.id,
                            }
                        )"
                            class="inline-flex items-center px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                            <Edit class="w-4 h-4 mr-1" />
                            Modifier
                        </Link>

                        <!-- Supprimer -->
                        <button v-if="can('ORG_DEVIS_QUANTITATIF_DELETE')" @click="confirmDelete()"
                            class="inline-flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors">
                            <Trash2 class="w-4 h-4 mr-1" />
                            Supprimer
                        </button>
                    </div>
                </CardContent>
            </Card>
        </AdminLayout>
    </SidebarProvider>
</template>