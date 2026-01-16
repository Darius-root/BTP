<script setup lang="ts">
import { computed, reactive } from "vue";
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

const props = defineProps<{
    batiment: any;
    devis: any;
    corpsEtats: any[];
    unites: any[];
    devise: String;
}>();

// Construire une map de forms (un useForm par corps d'état)
const formsMap = reactive<Record<number, any>>({});

props.corpsEtats.forEach((ce) => {
    formsMap[ce.id] = useForm({
        devis_id: props.devis.id,
        corps_etat_id: ce.id,
        intitule: ce.intitule,
        lots: ce.lots?.length
            ? ce.lots.map((l: any) => ({
                  code: l.code ?? "",
                  intitule: l.intitule ?? "",
                  composants:
                      l.composants?.map((c: any) => ({
                          designation: c.designation ?? "",
                          unite_id: c.unite_id ?? null,
                          quantite: c.quantite ?? 0,
                          prix_unitaire: c.prix_unitaire ?? 0,
                      })) ?? [],
              }))
            : [],
    });
});

/* HELPERS ciblés par corps d'état (utiliser l'id du CE) */
const addLot = (ceId: number) => {
    formsMap[ceId].lots.push({
        code: "",
        intitule: "",
        composants: [],
    });
};

const addComposant = (ceId: number, lotIndex: number) => {
    formsMap[ceId].lots[lotIndex].composants.push({
        designation: "",
        unite_id: null,
        quantite: 0,
        prix_unitaire: 0,
    });
};

const removeLot = (ceId: number, lotIndex: number) => {
    formsMap[ceId].lots.splice(lotIndex, 1);
};

const removeComposant = (ceId: number, lotIndex: number, compIndex: number) => {
    formsMap[ceId].lots[lotIndex].composants.splice(compIndex, 1);
};

const error = (ceId: number, path: string) => {
    return formsMap[ceId]?.errors?.[path] ?? null;
};

/* TOTALS */
const totalLot = (lot: any) =>
    (lot.composants || []).reduce(
        (sum: number, c: any) => sum + c.quantite * c.prix_unitaire,
        0
    );

const totalGeneral = computed(() =>
    Object.values(formsMap).reduce(
        (sum: number, f: any) =>
            sum +
            (f.lots || []).reduce((ls: number, l: any) => ls + totalLot(l), 0),
        0
    )
);

const submitOne = (ceId: number) => {
    const f = formsMap[ceId];

    f.put(
        route("batiments.devis-estimatif-quantitatif.updateCorpsEtat", {
            batiment: props.batiment.id,
            devis_estimatif_quantitatif: props.devis.id,
        }),
        {
            preserveState: true,
            preserveScroll: true,
        }
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
                    <CardTitle>{{
                        devis.intitule ?? "Devis quantitatif"
                    }}</CardTitle>
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
                            v-for="ce in corpsEtats"
                            :key="ce.id"
                            :value="ce.id.toString()"
                        >
                            <div class="space-y-6">
                                <div
                                    v-for="(lot, lotIndex) in formsMap[ce.id]
                                        .lots"
                                    :key="lotIndex"
                                    class="border rounded-lg p-4 space-y-4"
                                >
                                    <div class="flex gap-2">
                                        <!-- Champ Code lot -->
                                        <div class="flex flex-1 flex-col px-4">
                                            <Input
                                                v-model="
                                                    formsMap[ce.id].lots[
                                                        lotIndex
                                                    ].code
                                                "
                                                placeholder="Code lot"
                                                class="input"
                                            />
                                            <p
                                                v-if="
                                                    error(
                                                        ce.id,
                                                        `lots.${lotIndex}.code`
                                                    )
                                                "
                                                class="text-sm text-red-600 mt-1"
                                            >
                                                {{
                                                    error(
                                                        ce.id,
                                                        `lots.${lotIndex}.code`
                                                    )
                                                }}
                                            </p>
                                        </div>

                                        <!-- Champ Intitulé -->
                                        <div class="flex flex-1 flex-col">
                                            <Input
                                                v-model="
                                                    formsMap[ce.id].lots[
                                                        lotIndex
                                                    ].intitule
                                                "
                                                placeholder="Intitulé"
                                                class="input"
                                            />
                                            <p
                                                v-if="
                                                    error(
                                                        ce.id,
                                                        `lots.${lotIndex}.intitule`
                                                    )
                                                "
                                                class="text-sm text-red-600 mt-1"
                                            >
                                                {{
                                                    error(
                                                        ce.id,
                                                        `lots.${lotIndex}.intitule`
                                                    )
                                                }}
                                            </p>
                                        </div>

                                        <!-- Bouton -->
                                        <Button
                                            variant="destructive"
                                            @click="
                                                removeLot(
                                                    ce.id,
                                                    Number(lotIndex)
                                                )
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
                                                v-for="(
                                                    comp, compIndex
                                                ) in lot.composants"
                                                :key="compIndex"
                                            >
                                                <td>
                                                    <Input
                                                        v-model="
                                                            formsMap[ce.id]
                                                                .lots[lotIndex]
                                                                .composants[
                                                                compIndex
                                                            ].designation
                                                        "
                                                        class="input"
                                                    />
                                                    <p
                                                        v-if="
                                                            error(
                                                                ce.id,
                                                                `lots.${lotIndex}.composants.${compIndex}.designation`
                                                            )
                                                        "
                                                        class="text-xs text-red-600 mt-1"
                                                    >
                                                        {{
                                                            error(
                                                                ce.id,
                                                                `lots.${lotIndex}.composants.${compIndex}.designation`
                                                            )
                                                        }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <Select
                                                        v-model="
                                                            formsMap[ce.id]
                                                                .lots[lotIndex]
                                                                .composants[
                                                                compIndex
                                                            ].unite_id
                                                        "
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
                                                    <p
                                                        v-if="
                                                            error(
                                                                ce.id,
                                                                `lots.${lotIndex}.composants.${compIndex}.unite_id`
                                                            )
                                                        "
                                                        class="text-xs text-red-600 mt-1"
                                                    >
                                                        {{
                                                            error(
                                                                ce.id,
                                                                `lots.${lotIndex}.composants.${compIndex}.unite_id`
                                                            )
                                                        }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <Input
                                                        type="number"
                                                        v-model.number="
                                                            formsMap[ce.id]
                                                                .lots[lotIndex]
                                                                .composants[
                                                                compIndex
                                                            ].quantite
                                                        "
                                                        class="input"
                                                    />
                                                    <p
                                                        v-if="
                                                            error(
                                                                ce.id,
                                                                `lots.${lotIndex}.composants.${compIndex}.quantite`
                                                            )
                                                        "
                                                        class="text-xs text-red-600 mt-1"
                                                    >
                                                        {{
                                                            error(
                                                                ce.id,
                                                                `lots.${lotIndex}.composants.${compIndex}.quantite`
                                                            )
                                                        }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <Input
                                                        type="number"
                                                        v-model.number="
                                                            formsMap[ce.id]
                                                                .lots[lotIndex]
                                                                .composants[
                                                                compIndex
                                                            ].prix_unitaire
                                                        "
                                                        class="input"
                                                    />
                                                    <p
                                                        v-if="
                                                            error(
                                                                ce.id,
                                                                `lots.${lotIndex}.composants.${compIndex}.prix_unitaire`
                                                            )
                                                        "
                                                        class="text-xs text-red-600 mt-1"
                                                    >
                                                        {{
                                                            error(
                                                                ce.id,
                                                                `lots.${lotIndex}.composants.${compIndex}.prix_unitaire`
                                                            )
                                                        }}
                                                    </p>
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
                                                                ce.id,
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
                                                {{ props.devise }}
                                            </span>
                                        </div>
                                    </div>

                                    <Button
                                        variant="secondary"
                                        @click="
                                            addComposant(
                                                ce.id,
                                                Number(lotIndex)
                                            )
                                        "
                                    >
                                        <Plus class="w-4 h-4 mr-1" />
                                        Ajouter composant
                                    </Button>
                                    <p
                                        v-if="
                                            error(
                                                ce.id,
                                                `lots.${lotIndex}.composants`
                                            )
                                        "
                                        class="text-sm text-red-600 mt-1"
                                    >
                                        {{
                                            error(
                                                ce.id,
                                                `lots.${lotIndex}.composants`
                                            )
                                        }}
                                    </p>
                                </div>

                                <Button
                                    variant="outline"
                                    @click="addLot(ce.id)"
                                >
                                    <Plus class="w-4 h-4 mr-1" />
                                    Ajouter un lot
                                </Button>

                                <p
                                    v-if="formsMap[ce.id].errors.lots"
                                    class="text-sm text-red-600"
                                >
                                    {{ formsMap[ce.id].errors.lots }}
                                </p>
                                <!-- Bouton pour valider ce corps d'état seulement -->
                                <div class="flex justify-end mt-4">
                                    <Button @click="submitOne(ce.id)">
                                        Enregistrer ce corps d'état
                                    </Button>
                                </div>
                            </div>
                        </TabsContent>
                    </Tabs>

                    <!-- FOOTER -->
                    <div
                        class="flex justify-between items-center mt-6 font-bold"
                    >
                        <span>
                            Total général :
                            {{ totalGeneral.toLocaleString() }}
                            {{ props.devise }}
                        </span>
                    </div>
                </CardContent>
            </Card>
        </AdminLayout>
    </SidebarProvider>
</template>
