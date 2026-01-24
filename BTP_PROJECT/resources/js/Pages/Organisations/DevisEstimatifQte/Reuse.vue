<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ArrowLeft, Copy, Building2, FileCheck } from "lucide-vue-next";

import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";

import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";

const props = defineProps<{
    batimentSource: {
        id: number;
        nom: string;
        code: string;
    };
    devisSource: {
        id: number;
        intitule: string;
        code: string;
        statut: string;
    };
    projets: Array<{
        id: number;
        nom: string;
        code: string;
        batiments: Array<{
            id: number;
            nom: string;
            code: string;
        }>;
    }>;
}>();

const form = useForm({
    batiment_cible_id: null as number | null,
    nouveau_code: "",
    nouvel_intitule: props.devisSource.intitule,
});

const projetSelectionne = ref<number | null>(null);

const batimentsDisponibles = computed(() => {
    if (!projetSelectionne.value) return [];

    const projet = props.projets.find((p) => p.id === projetSelectionne.value);
    return projet?.batiments || [];
});

const submit = () => {
    if (!form.batiment_cible_id) {
        alert("Veuillez sélectionner un bâtiment cible");
        return;
    }

    // ✅ Correction : passer batiment ET devis dans l'URL
    form.post(
        route("batiments.devis-estimatif-quantitatif.reuse", {
            batiment: props.batimentSource.id,
            devis: props.devisSource.id
        }),
        {
            onError: (errors) => {
                console.error("Erreurs de validation:", errors);
            },
        }
    );
};

// ✅ Correction : passer les deux paramètres requis
const annuler = () => {
    window.location.href = route(
        "batiments.devis-estimatif-quantitatif.show",
        {
            batiment: props.batimentSource.id,
            devis_estimatif_quantitatif: props.devisSource.id
        }
    );
};
</script>

<template>

    <Head title="Réutiliser le devis" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb pageTitle="Réutiliser un devis quantitatif" />

            <div class="p-6 max-w-4xl mx-auto">
                <form @submit.prevent="submit">
                    <!-- Devis source -->
                    <Card class="mb-6">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <FileCheck class="w-5 h-5 text-blue-600" />
                                Devis source
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="grid grid-cols-2 gap-4">
                            <div>
                                <Label class="text-muted-foreground">Bâtiment</Label>
                                <p class="font-medium">
                                    {{ batimentSource.nom }} ({{ batimentSource.code }})
                                </p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Devis</Label>
                                <p class="font-medium">
                                    {{ devisSource.intitule }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Code: {{ devisSource.code }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Sélection du bâtiment cible -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Copy class="w-5 h-5 text-green-600" />
                                Bâtiment cible
                            </CardTitle>
                        </CardHeader>

                        <CardContent class="space-y-6">
                            <div v-if="projets.length === 0" class="text-center py-8 text-muted-foreground">
                                <Building2 class="w-12 h-12 mx-auto mb-4 opacity-50" />
                                <p class="font-medium">
                                    Aucun bâtiment disponible
                                </p>
                                <p class="text-sm">
                                    Tous les bâtiments de l'organisation ont déjà un devis
                                </p>
                            </div>

                            <template v-else>
                                <!-- Sélection du projet -->
                                <div class="space-y-2">
                                    <Label>Projet</Label>
                                    <Select v-model="projetSelectionne">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Sélectionnez un projet" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="projet in projets" :key="projet.id" :value="projet.id">
                                                {{ projet.nom }} ({{ projet.code }}) -
                                                {{ projet.batiments.length }} bâtiment(s)
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <!-- Sélection du bâtiment -->
                                <div v-if="projetSelectionne" class="space-y-2">
                                    <Label>Bâtiment cible</Label>
                                    <Select v-model="form.batiment_cible_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Sélectionnez un bâtiment" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="bat in batimentsDisponibles" :key="bat.id"
                                                :value="bat.id">
                                                {{ bat.nom }} ({{ bat.code }})
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <span v-if="form.errors.batiment_cible_id" class="text-red-600 text-sm">
                                        {{ form.errors.batiment_cible_id }}
                                    </span>
                                </div>

                                <!-- Code du nouveau devis -->
                                <div class="space-y-2">
                                    <Label>Code du nouveau devis *</Label>
                                    <Input v-model="form.nouveau_code" placeholder="DQ-2026-002" />
                                    <span v-if="form.errors.nouveau_code" class="text-red-600 text-sm">
                                        {{ form.errors.nouveau_code }}
                                    </span>
                                </div>

                                <!-- Intitulé du nouveau devis -->
                                <div class="space-y-2">
                                    <Label>Intitulé du nouveau devis (optionnel)</Label>
                                    <Input v-model="form.nouvel_intitule"
                                        placeholder="Reprend l'intitulé du devis source par défaut" />
                                    <span v-if="form.errors.nouvel_intitule" class="text-red-600 text-sm">
                                        {{ form.errors.nouvel_intitule }}
                                    </span>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-4 border-t">
                                    <Button type="button" variant="outline" @click="annuler">
                                        <ArrowLeft class="w-4 h-4 mr-2" />
                                        Annuler
                                    </Button>

                                    <Button type="submit" :disabled="form.processing || !form.batiment_cible_id"
                                        class="bg-green-600 hover:bg-green-700">
                                        <Copy class="w-4 h-4 mr-2" />
                                        Réutiliser le devis
                                    </Button>
                                </div>
                            </template>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>