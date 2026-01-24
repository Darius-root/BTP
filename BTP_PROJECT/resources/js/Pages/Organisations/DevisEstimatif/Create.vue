<script setup lang="ts">
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import { Button } from "@/components/ui/button";
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

// Icônes Lucide
import {
    PlusCircle,
    Trash2,
    Plus,
    XCircle,
    CheckCircle,
    Calculator,
    Sigma,
} from "lucide-vue-next";

interface Niveau {
    id: number;
    nom: string;
}

interface Unite {
    id: number;
    libelle: string;
}
interface Batiment {
    id: number;
    code: string;
    nom: string;
    localisation?: string;
    description?: string;
    projet_id: number;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    niveaux: Niveau[];
    unites: Unite[];
    batiment: Batiment;
    devise: String
}>();

const form = useForm({
    intitule: "",
    batiment_id:props.batiment.id,
    niveaux: [
        {
            niveau_id: null as number | null,
            composants: [
                {
                    code: "",
                    piece: "",
                    unite_id: null as number | null,
                    qte: 0,
                    prix_unitaire: 0,
                },
            ],
        },
    ],
});

// Ajouter / Supprimer niveau
const addNiveau = () => {
    form.niveaux.push({
        niveau_id: null,
        composants: [
            {
                code: "",
                piece: "",
                unite_id: null,
                qte: 0,
                prix_unitaire: 0,
            },
        ],
    });
};

const removeNiveau = (index: number) => {
    form.niveaux.splice(index, 1);
};

// Ajouter / Supprimer composant
const addComposant = (niveauIndex: number) => {
    form.niveaux[niveauIndex].composants.push({
        code: "",
        piece: "",
        unite_id: null,
        qte: 0,
        prix_unitaire: 0,
    });
};

const removeComposant = (niveauIndex: number, compIndex: number) => {
    form.niveaux[niveauIndex].composants.splice(compIndex, 1);
};

// Niveaux disponibles
const availableNiveaux = (currentIndex: number) => {
    const selectedIds = form.niveaux
        .filter((_, i) => i !== currentIndex)
        .map((n) => n.niveau_id)
        .filter((id): id is number => id !== null);
    return props.niveaux.filter((n) => !selectedIds.includes(n.id));
};

// Sous-totaux
const niveauSubtotal = (niveau: (typeof form.niveaux)[number]) => {
    return niveau.composants.reduce((sum, comp) => {
        return sum + comp.qte * comp.prix_unitaire;
    }, 0);
};

// Total général
const totalGeneral = computed(() => {
    return form.niveaux.reduce((sum, niveau) => {
        return sum + niveauSubtotal(niveau);
    }, 0);
});

const submit = () => {
    form.post(route("batiments.devisestimatif.store", { batiment: props.batiment.id }));
};
</script>

<template>
    <Head title="Créer un Devis Estimatif" />
    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb
                :pageTitle="`Créer un Devis Estimatif ${batiment.nom}`"
            />
            <div class="p-6 space-y-6">
                     <form @submit.prevent="submit" >

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Calculator class="w-5 h-5 text-blue-600" />
                            Créer un Devis Estimatif pour le batiment :
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                                 <input type="hidden" name="batiment_id" :value=" batiment.id " />
                        <div class="space-y-1">
                            <Label for="intitule">Intitulé</Label>
                            <Input
                                id="intitule"
                                v-model="form.intitule"
                                placeholder="Nom du devis"
                            />
                            <span
                                v-if="form.errors.intitule"
                                class="text-red-600 text-sm"
                            >
                                {{ form.errors.intitule }}
                            </span>
                        </div>

                        <!-- Niveaux -->
                        <div
                            v-for="(niveau, nIndex) in form.niveaux"
                            :key="nIndex"
                            class="border p-4 rounded space-y-4 bg-gray-50 dark:bg-black"
                        >
                            <div class="flex justify-between items-center">
                                <Label class="font-semibold">Niveau</Label>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    @click="removeNiveau(nIndex)"
                                >
                                    <Trash2 class="w-4 h-4 mr-1" /> Supprimer
                                    Niveau
                                </Button>
                            </div>

                            <Select v-model="niveau.niveau_id">
                                <SelectTrigger class="w-full">
                                    <SelectValue
                                        placeholder="-- Choisir un niveau --"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="n in availableNiveaux(nIndex)"
                                        :key="n.id"
                                        :value="n.id"
                                    >
                                        {{ n.nom }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <span
                                v-if="
                                    form.errors[`niveaux.${nIndex}.niveau_id`]
                                "
                                class="text-red-600 mb-4 text-sm"
                            >
                                {{ form.errors[`niveaux.${nIndex}.niveau_id`] }}
                            </span>

                            <!-- Composants -->
                            <div
                                v-for="(comp, cIndex) in niveau.composants"
                                :key="cIndex"
                                class="grid grid-cols-7 gap-2 items-stretch justify-start"
                            >
                                <div>
                                    <Label>Code</Label>
                                    <Input
                                        v-model="comp.code"
                                        placeholder="ASxx"
                                    />
                                    <span
                                        v-if="
                                            form.errors[
                                                `niveaux.${nIndex}.composants.${cIndex}.code`
                                            ]
                                        "
                                        class="text-red-600 text-sm text-wrap"
                                    >
                                        {{
                                            form.errors[
                                                `niveaux.${nIndex}.composants.${cIndex}.code`
                                            ]
                                        }}
                                    </span>
                                </div>
                                <div class="col-span-2">
                                    <Label>Désignation</Label>
                                    <Input
                                        v-model="comp.piece"
                                        placeholder="Barre de fixation"
                                    />
                                    <span
                                        v-if="
                                            form.errors[
                                                `niveaux.${nIndex}.composants.${cIndex}.piece`
                                            ]
                                        "
                                        class="text-red-600 text-sm"
                                    >
                                        {{
                                            form.errors[
                                                `niveaux.${nIndex}.composants.${cIndex}.piece`
                                            ]
                                        }}
                                    </span>
                                </div>
                                <div>
                                    <Label>Unité</Label>
                                    <Select v-model="comp.unite_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue
                                                placeholder="-- Unité --"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="u in props.unites"
                                                :key="u.id"
                                                :value="u.id"
                                                >{{ u.libelle }}</SelectItem
                                            >
                                        </SelectContent>
                                    </Select>
                                    <span
                                        v-if="
                                            form.errors[
                                                `niveaux.${nIndex}.composants.${cIndex}.unite_id`
                                            ]
                                        "
                                        class="text-red-600 text-sm"
                                    >
                                        {{
                                            form.errors[
                                                `niveaux.${nIndex}.composants.${cIndex}.unite_id`
                                            ]
                                        }}
                                    </span>
                                </div>
                                <div>
                                    <Label>Qté</Label>
                                    <Input
                                        type="number"
                                        v-model.number="comp.qte"
                                        placeholder="0"
                                    />
                                    <span
                                        v-if="
                                            form.errors[
                                                `niveaux.${nIndex}.composants.${cIndex}.qte`
                                            ]
                                        "
                                        class="text-red-600 text-sm"
                                    >
                                        {{
                                            form.errors[
                                                `niveaux.${nIndex}.composants.${cIndex}.qte`
                                            ]
                                        }}
                                    </span>
                                </div>
                                <div>
                                    <Label>Prix Unitaire</Label>
                                    <Input
                                        type="number"
                                        v-model.number="comp.prix_unitaire"
                                        placeholder="0"
                                    />
                                    <span
                                        v-if="
                                            form.errors[
                                                `niveaux.${nIndex}.composants.${cIndex}.prix_unitaire`
                                            ]
                                        "
                                        class="text-red-600 text-sm"
                                    >
                                        {{
                                            form.errors[
                                                `niveaux.${nIndex}.composants.${cIndex}.prix_unitaire`
                                            ]
                                        }}
                                    </span>
                                </div>

                                <!-- Bouton sur la même ligne -->
                                <div
                                    class="flex items-start mt-4 justify-center"
                                >
                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        @click="removeComposant(nIndex, cIndex)"
                                    >
                                        <XCircle class="w-4 h-4 mr-1" />
                                        Supprimer
                                    </Button>
                                </div>
                            </div>

                            <Button
                                size="sm"
                                variant="outline"
                                type="su"
                                @click.prevent="addComposant(nIndex)"
                            >
                                <Plus class="w-4 h-4 mr-1" /> Ajouter un
                                composant
                            </Button>

                            <!-- Sous-total -->
                            <div
                                class="flex justify-end items-center bg-white p-2 rounded mt-2 shadow-sm"
                            >
                                <Calculator
                                    class="w-4 h-4 mr-2 text-gray-600"
                                />
                                <span class="font-semibold"
                                    >Sous-total :
                                    {{
                                        niveauSubtotal(niveau).toLocaleString()
                                    }}
                                    {{devise}}</span
                                >
                            </div>
                        </div>

                        <Button
                            size="sm"
                            variant="secondary"
                            @click.prevent="addNiveau"
                        >
                            <PlusCircle class="w-4 h-4 mr-1" /> Ajouter un
                            niveau
                        </Button>

                        <!-- Total général -->
                        <div
                            class="flex justify-end items-center text-green-600 font-bold text-lg mt-6"
                        >
                            <Sigma class="w-5 h-5 mr-2" />
                            Total Général :
                            {{ totalGeneral.toLocaleString() }} {{devise}}
                        </div>

                        <div class="mt-4 flex justify-end">
                            <Button  :disabled="form.processing"

                                class="bg-blue-600 text-white hover:bg-blue-700"
                            >
                                <CheckCircle class="w-4 h-4 mr-1" /> Créer Devis
                            </Button>
                        </div>
                    </CardContent>
                </Card></form>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>
