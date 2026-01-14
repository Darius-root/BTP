<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";

import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";

const props = defineProps<{
    batiment: {
        id: number;
        nom: string;
    };
    devis: {
        id: number;
        intitule: string;
        code: string;
        statut: string;
    };
}>();

const form = useForm({
    intitule: props.devis.intitule,
    code: props.devis.code,
    statut: props.devis.statut,
});

const submit = () => {
    form.put(route("batiments.devis-estimatif-quantitatif.update", { batiment: props.batiment.id , devis_estimatif_quantitatif:0}), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Édition du devis" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="`Édition devis – ${batiment.nom}`" />

            <Card class="m-6 max-w-xl mx-auto">
                <CardHeader>
                    <CardTitle>Modifier le devis</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">

                    <!-- Intitulé -->
                    <div class="flex flex-col">
                        <Label for="intitule">Intitulé</Label>
                        <Input id="intitule" v-model="form.intitule" placeholder="Nom du devis" />
                        <p v-if="form.errors.intitule" class="text-red-600 text-sm mt-2">
                            {{ form.errors.intitule }}
                        </p>
                    </div>

                    <!-- Code -->
                    <div class="flex flex-col">
                        <Label for="code">Code</Label>
                        <Input id="code" v-model="form.code" placeholder="Code unique" />
                        <p v-if="form.errors.code" class="text-red-600 text-sm mt-2">
                            {{ form.errors.code }}
                        </p>
                    </div>

                 

                    <!-- Bouton -->
                    <div class="flex justify-end mt-4">
                        <Button :disabled="form.processing" @click="submit">Enregistrer</Button>
                    </div>
                </CardContent>
            </Card>
        </AdminLayout>
    </SidebarProvider>
</template>
