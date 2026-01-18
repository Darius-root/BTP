<script setup>
import { ref, computed, watch } from "vue";
import { router, Head, useForm } from "@inertiajs/vue3";
import { Plus, Trash2, Save } from "lucide-vue-next";

import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";

const props = defineProps({
    template: {
        type: Object,
        required: true,
    },
    batimentSource: {
        type: Object,
        required: true,
    },
    projetSource: {
        type: Object,
        required: true,
    },
    composantsSource: {
        type: Array,
        required: true,
    },
    projets: {
        type: Array,
        required: true,
    },
    niveaux: {
        type: Array,
        required: true,
    },
    unites: {
        type: Array,
        required: true,
    },
    devise: {
        type: String,
        default: "MAD",
    },
});

// État local
const selectedProjetId = ref(null);
const batimentsDisponibles = ref([]);
const isLoadingBatiments = ref(false);

// Formulaire Inertia
const form = useForm({
    batiment_id: null,
    intitule: `${props.template.intitule} - Copie`,
    niveaux: props.composantsSource.map((niveau) => ({
        niveau_id: niveau.niveau_id,
        composants: niveau.composants.map((comp) => ({ ...comp })),
    })),
});

// Charger les bâtiments disponibles quand un projet est sélectionné
watch(selectedProjetId, async (newProjetId) => {
    console.log('Projet sélectionné:', newProjetId);

    if (!newProjetId) {
        batimentsDisponibles.value = [];
        form.batiment_id = null;
        return;
    }

    isLoadingBatiments.value = true;
    form.batiment_id = null; // Réinitialiser le bâtiment sélectionné

    try {
        const url = route("api.projets.batiments-disponibles", newProjetId);
        console.log('Appel API:', url);

        const response = await fetch(url);

        if (response.ok) {
            const data = await response.json();
            console.log('Bâtiments reçus:', data);
            batimentsDisponibles.value = data;
        } else {
            const errorText = await response.text();
            console.error("Erreur lors du chargement des bâtiments:", response.status, errorText);
            batimentsDisponibles.value = [];
        }
    } catch (error) {
        console.error("Erreur:", error);
        batimentsDisponibles.value = [];
    } finally {
        isLoadingBatiments.value = false;
    }
});

// Calculer le total par niveau
const calculerTotalNiveau = (niveau) => {
    return niveau.composants.reduce((total, comp) => {
        return total + (parseFloat(comp.qte) || 0) * (parseFloat(comp.prix_unitaire) || 0);
    }, 0);
};

// Calculer le total général
const totalGeneral = computed(() => {
    return form.niveaux.reduce((total, niveau) => {
        return total + calculerTotalNiveau(niveau);
    }, 0);
});

// Ajouter un composant à un niveau
const ajouterComposant = (niveauIndex) => {
    form.niveaux[niveauIndex].composants.push({
        code: "",
        piece: "",
        unite_id: props.unites[0]?.id || null,
        qte: 0,
        prix_unitaire: 0,
    });
};

// Supprimer un composant
const supprimerComposant = (niveauIndex, composantIndex) => {
    form.niveaux[niveauIndex].composants.splice(composantIndex, 1);
};

// Ajouter un niveau
const ajouterNiveau = () => {
    form.niveaux.push({
        niveau_id: props.niveaux[0]?.id || null,
        composants: [
            {
                code: "",
                piece: "",
                unite_id: props.unites[0]?.id || null,
                qte: 0,
                prix_unitaire: 0,
            },
        ],
    });
};

// Supprimer un niveau
const supprimerNiveau = (niveauIndex) => {
    if (form.niveaux.length > 1) {
        form.niveaux.splice(niveauIndex, 1);
    }
};

// Soumettre le formulaire
const soumettre = () => {
    if (!form.batiment_id) {
        alert("Veuillez sélectionner un bâtiment");
        return;
    }

    form.post(
        route("templates.reuse.store", props.template.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                console.log("Devis créé avec succès");
            },
            onError: (errors) => {
                console.error("Erreurs:", errors);
            },
        }
    );

};

// Annuler et retourner
const annuler = () => {
    router.visit(route("templates.show", props.template.id));
};

// Obtenir le nom d'un niveau par ID
const getNiveauNom = (niveauId) => {
    const niveau = props.niveaux.find((n) => n.id === niveauId);
    return niveau ? niveau.nom : "Niveau inconnu";
};
</script>

<template>
    <SidebarProvider>
        <AdminLayout>

            <Head :title="`Réutiliser le template - ${template.code}`" />

            <PageBreadcrumb :pageTitle="`Réutiliser le template ${template.code}`" />

            <!-- INFO TEMPLATE SOURCE -->
            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Template source</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <strong class="text-gray-600">Code</strong><br />
                        <span class="font-mono font-semibold text-blue-600">{{ template.code }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-600">Intitulé</strong><br />
                        {{ template.intitule }}
                    </div>
                    <div>
                        <strong class="text-gray-600">Projet source</strong><br />
                        {{ projetSource.nom }}
                    </div>
                    <div>
                        <strong class="text-gray-600">Bâtiment source</strong><br />
                        {{ batimentSource.nom }}
                    </div>
                </CardContent>
            </Card>

            <!-- FORMULAIRE -->
            <form @submit.prevent="soumettre">
                <!-- SÉLECTION PROJET ET BÂTIMENT -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Destination</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Intitulé -->
                        <div>
                            <Label for="intitule">Intitulé du nouveau devis</Label>
                            <Input id="intitule" v-model="form.intitule" type="text" required
                                :class="{ 'border-red-500': form.errors.intitule }" />
                            <p v-if="form.errors.intitule" class="text-red-500 text-sm mt-1">
                                {{ form.errors.intitule }}
                            </p>
                        </div>

                        <!-- Projet -->
                        <div>
                            <Label for="projet">Projet de destination</Label>
                            <Select v-model="selectedProjetId" @update:modelValue="(val) => {
                                console.log('Projet changé:', val, typeof val);
                                selectedProjetId = val;
                            }">
                                <SelectTrigger>
                                    <SelectValue placeholder="Sélectionnez un projet" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="projet in projets" :key="projet.id" :value="projet.id">
                                        {{ projet.code }} - {{ projet.nom }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="selectedProjetId" class="text-xs text-gray-500 mt-1">
                                Projet sélectionné : {{ selectedProjetId }}
                            </p>
                        </div>

                        <!-- Bâtiment -->
                        <div>
                            <Label for="batiment">Bâtiment de destination</Label>
                            <Select v-model="form.batiment_id" :disabled="!selectedProjetId || isLoadingBatiments"
                                @update:modelValue="(val) => {
                                    console.log('Bâtiment changé:', val, typeof val);
                                    form.batiment_id = val;
                                }">
                                <SelectTrigger :class="{ 'border-red-500': form.errors.batiment_id }">
                                    <SelectValue :placeholder="isLoadingBatiments
                                            ? 'Chargement...'
                                            : selectedProjetId
                                                ? 'Sélectionnez un bâtiment'
                                                : 'Sélectionnez d\'abord un projet'
                                        " />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="batiment in batimentsDisponibles" :key="batiment.id"
                                        :value="batiment.id">
                                        {{ batiment.code }} - {{ batiment.nom }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.batiment_id" class="text-red-500 text-sm mt-1">
                                {{ form.errors.batiment_id }}
                            </p>
                            <p v-if="
                                selectedProjetId &&
                                !isLoadingBatiments &&
                                batimentsDisponibles.length === 0
                            " class="text-amber-600 text-sm mt-1">
                                Aucun bâtiment disponible (sans devis) dans ce projet.
                            </p>
                            <p v-if="selectedProjetId && !isLoadingBatiments" class="text-xs text-gray-500 mt-1">
                                {{ batimentsDisponibles.length }} bâtiment(s) disponible(s)
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- NIVEAUX ET COMPOSANTS -->
                <Card class="mb-6">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle>Composants du devis</CardTitle>
                        <Button type="button" variant="outline" size="sm" @click="ajouterNiveau">
                            <Plus class="w-4 h-4 mr-1" />
                            Ajouter un niveau
                        </Button>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div v-for="(niveau, niveauIndex) in form.niveaux" :key="niveauIndex"
                            class="border rounded-lg p-4 space-y-4">
                            <!-- HEADER NIVEAU -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4 flex-1">
                                    <Label>Niveau</Label>
                                    <Select v-model="niveau.niveau_id" required>
                                        <SelectTrigger class="w-64">
                                            <SelectValue placeholder="Sélectionnez un niveau" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="niv in niveaux" :key="niv.id" :value="niv.id">
                                                {{ niv.nom }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Badge variant="secondary">
                                        Total: {{ calculerTotalNiveau(niveau).toLocaleString() }}
                                        {{ devise }}
                                    </Badge>
                                </div>

                                <div class="flex gap-2">
                                    <Button type="button" variant="outline" size="sm"
                                        @click="ajouterComposant(niveauIndex)">
                                        <Plus class="w-4 h-4" />
                                    </Button>
                                    <Button v-if="form.niveaux.length > 1" type="button" variant="outline" size="sm"
                                        class="text-red-600" @click="supprimerNiveau(niveauIndex)">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>

                            <!-- COMPOSANTS -->
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm border">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr>
                                            <th class="border p-2 text-left w-32">Code</th>
                                            <th class="border p-2 text-left">Pièce</th>
                                            <th class="border p-2 text-left w-32">Unité</th>
                                            <th class="border p-2 text-right w-24">Qté</th>
                                            <th class="border p-2 text-right w-32">Prix unit.</th>
                                            <th class="border p-2 text-right w-32">Montant</th>
                                            <th class="border p-2 w-16"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(
comp, compIndex
                                            ) in niveau.composants" :key="compIndex">
                                            <td class="border p-2">
                                                <Input v-model="comp.code" type="text" required class="w-full" />
                                            </td>
                                            <td class="border p-2">
                                                <Input v-model="comp.piece" type="text" required class="w-full" />
                                            </td>
                                            <td class="border p-2">
                                                <Select v-model="comp.unite_id" required>
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem v-for="unite in unites" :key="unite.id"
                                                            :value="unite.id">
                                                            {{ unite.libelle }}
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </td>
                                            <td class="border p-2">
                                                <Input v-model.number="comp.qte" type="number" step="0.01" min="0"
                                                    required class="w-full text-right" />
                                            </td>
                                            <td class="border p-2">
                                                <Input v-model.number="comp.prix_unitaire" type="number" step="0.01"
                                                    min="0" required class="w-full text-right" />
                                            </td>
                                            <td class="border p-2 text-right font-semibold">
                                                {{
                                                    (
                                                        (parseFloat(comp.qte) || 0) *
                                                        (parseFloat(comp.prix_unitaire) || 0)
                                                    ).toLocaleString()
                                                }}
                                            </td>
                                            <td class="border p-2 text-center">
                                                <Button v-if="niveau.composants.length > 1" type="button"
                                                    variant="ghost" size="sm" class="text-red-600" @click="
                                                        supprimerComposant(
                                                            niveauIndex,
                                                            compIndex
                                                        )
                                                        ">
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- TOTAL GÉNÉRAL -->
                <Card class="mb-6 bg-blue-50 dark:bg-blue-950/20 border-blue-200">
                    <CardContent class="flex justify-between items-center py-4">
                        <span class="text-lg font-bold">Total général</span>
                        <span class="text-xl font-bold text-blue-600">
                            {{ totalGeneral.toLocaleString() }} {{ devise }}
                        </span>
                    </CardContent>
                </Card>

                <!-- ACTIONS -->
                <div class="flex justify-end gap-3">
                    <Button type="button" variant="outline" @click="annuler">
                        Annuler
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="w-4 h-4 mr-1" />
                        {{ form.processing ? "Création..." : "Créer le devis" }}
                    </Button>
                </div>
            </form>
        </AdminLayout>
    </SidebarProvider>
</template>