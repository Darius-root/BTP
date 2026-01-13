<script setup lang="ts">
import { computed } from "vue";
import { Head, useForm } from "@inertiajs/vue3";

import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";

import { Button } from "@/components/ui/button";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/components/ui/tabs";
import { Plus, Trash } from "lucide-vue-next";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from "@/components/ui/select";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

/* =========================
   PROPS
========================= */
const props = defineProps<{
    batiment: any;
    devis: any;
    corpsEtats: any[];
    unites: any[];
}>();

/* =========================
   FORM INITIALISATION
========================= */
const form = useForm({
    devis_id: props.devis.id,
    corps_etats: props.corpsEtats.map((ce) => ({
        corps_etat_id: ce.id,
        intitule: ce.intitule,
        lots: [],
    })),
});

/* =========================
   HELPERS
========================= */
const addLot = (ceIndex: number) => {
    form.corps_etats[ceIndex].lots.push({
        code: "",
        intitule: "",
        composants: [],
    });
};

const addComposant = (ceIndex: number, lotIndex: number) => {
    form.corps_etats[ceIndex].lots[lotIndex].composants.push({
        designation: "",
        unite_id: null,
        quantite: 0,
        prix_unitaire: 0,
    });
};

const removeLot = (ceIndex: number, lotIndex: number) => {
    form.corps_etats[ceIndex].lots.splice(lotIndex, 1);
};

const removeComposant = (
    ceIndex: number,
    lotIndex: number,
    compIndex: number
) => {
    form.corps_etats[ceIndex].lots[lotIndex].composants.splice(compIndex, 1);
};

/* =========================
   TOTALS
========================= */
const totalLot = (lot: any) =>
    lot.composants.reduce((sum, c) => sum + c.quantite * c.prix_unitaire, 0);

const totalGeneral = computed(() =>
    form.corps_etats.reduce(
        (sum, ce) => sum + ce.lots.reduce((ls, l) => ls + totalLot(l), 0),
        0
    )
);

/* =========================
   SUBMIT
========================= */
const submit = () => {
    form.post(
        route("batiments.devis-estimatif-quantitatif.store", props.devis.id)
    );
};
</script>
<template>
    <Head title="Édition devis quantitatif" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb
                :pageTitle="`Devis quantitatif – ${batiment.nom}`"
            />

            <Card class="m-6">
                <CardHeader>
                    <CardTitle>
                        {{ devis.intitule ?? "Devis quantitatif" }}
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

                        <!-- CONTENU PAR CORPS D’ETAT -->
                        <TabsContent
                            v-for="(ce, ceIndex) in form.corps_etats"
                            :key="ce.corps_etat_id"
                            :value="ce.corps_etat_id.toString()"
                        >
                            <div class="space-y-6">
                                <!-- LOTS -->
                                <div
                                    v-for="(lot, lotIndex) in ce.lots"
                                    :key="lotIndex"
                                    class="border rounded-lg p-4 space-y-4"
                                >
                                    <div class="flex gap-2">
                                        <Input
                                            v-model="lot.code"
                                            placeholder="Code lot"
                                            class="input"
                                        />

                                        <Input
                                            v-model="lot.intitule"
                                            placeholder="Intitulé"
                                            class="input"
                                        />
                                        <Button
                                            variant="destructive"
                                            @click="
                                                removeLot(ceIndex, lotIndex)
                                            "
                                        >
                                            <Trash class="w-4 h-4" />
                                        </Button>
                                    </div>

                                    <!-- COMPOSANTS -->
                                    <table
                                        class="w-full text-sm border-separate border-spacing-y-2 [&_td]:px-4 [&_th]:px-4"
                                    >
                                        <thead>
                                            <tr class="text-left">
                                                <th>Désignation</th>
                                                <th>Unité</th>
                                                <th>Qté</th>
                                                <th>PU</th>
                                                <th>Montant</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                class=""
                                                v-for="(
                                                    comp, compIndex
                                                ) in lot.composants"
                                                :key="compIndex"
                                            >
                                                <td>
                                                    <Input
                                                        v-model="
                                                            comp.designation
                                                        "
                                                        class="input"
                                                    />
                                                </td>
                                                <td>
                                                    <Select
                                                        v-model="comp.unite_id"
                                                    >
                                                        <SelectTrigger
                                                            class="w-full"
                                                        >
                                                            <SelectValue
                                                                placeholder="-- Choisir un niveau --"
                                                            />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem
                                                                v-for="u in unites"
                                                                :key="u.id"
                                                                :value="u.id"
                                                            >
                                                                {{ u.code }}
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </td>
                                                <td>
                                                    <Input
                                                        type="number"
                                                        v-model.number="
                                                            comp.quantite
                                                        "
                                                        class="input"
                                                    />
                                                </td>
                                                <td>
                                                    <Input
                                                        type="number"
                                                        v-model.number="
                                                            comp.prix_unitaire
                                                        "
                                                        class="input"
                                                    />
                                                </td>
                                                <td>
                                                    {{
                                                        (
                                                            comp.quantite *
                                                            comp.prix_unitaire
                                                        ).toLocaleString()
                                                    }}
                                                </td>
                                                <td>
                                                    <Button
                                                        variant="ghost"
                                                        @click="
                                                            removeComposant(
                                                                Number(ceIndex),
                                                                Number(
                                                                    lotIndex
                                                                ),
                                                                Number(
                                                                    compIndex
                                                                )
                                                            )
                                                        "
                                                    >
                                                        <Trash
                                                            class="w-4 h-4"
                                                        />
                                                    </Button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <!-- SOUS-TOTAL DU LOT -->
                                    <div class="flex justify-end mt-2">
                                        <div
                                            class="bg-gray-100 rounded-lg px-4 py-2 font-semibold text-sm"
                                        >
                                            Sous-total {{ lot.intitule }} :
                                            <span class="ml-2 text-primary">
                                                {{
                                                    totalLot(
                                                        lot
                                                    ).toLocaleString()
                                                }}
                                                FCFA
                                            </span>
                                        </div>
                                    </div>

                                    <Button
                                        variant="secondary"
                                        @click="addComposant(ceIndex, lotIndex)"
                                    >
                                        <Plus class="w-4 h-4 mr-1" />
                                        Ajouter composant
                                    </Button>
                                </div>

                                <Button
                                    variant="outline"
                                    @click="addLot(ceIndex)"
                                >
                                    <Plus class="w-4 h-4 mr-1" />
                                    Ajouter un lot
                                </Button>
                            </div>
                        </TabsContent>
                    </Tabs>

                    <!-- FOOTER -->
                    <div
                        class="flex justify-between items-center mt-6 font-bold"
                    >
                        <span>
                            Total général :
                            {{ totalGeneral.toLocaleString() }} FCFA
                        </span>

                        <Button @click="submit"> Enregistrer le devis </Button>
                    </div>
                </CardContent>
            </Card>
        </AdminLayout>
    </SidebarProvider>
</template>
