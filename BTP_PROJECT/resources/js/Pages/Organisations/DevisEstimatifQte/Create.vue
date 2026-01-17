<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import AdminLayout from "@/components/layout/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";

import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";

import { FilePlus2, CheckCircle } from "lucide-vue-next";

const props = defineProps<{
    batiment: {
        id: number;
        nom: string;
        code: string;
    };
}>();

const form = useForm({
    intitule: "",
    code: "",
    is_template: false,
});

const submit = () => {
    form.post(
        route("batiments.devis-estimatif-quantitatif.store", { batiment: props.batiment.id })
    );
};
</script>

<template>
    <Head title="Créer un devis quantitatif" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb
                :pageTitle="`Créer un devis quantitatif – ${batiment.nom}`"
            />

            <div class="p-6 max-w-3xl mx-auto">
                <form @submit.prevent="submit">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <FilePlus2 class="w-5 h-5 text-blue-600" />
                                Nouveau devis estimatif quantitatif
                            </CardTitle>
                        </CardHeader>

                        <CardContent class="space-y-4">
                            <!-- Intitulé -->
                            <div class="space-y-1">
                                <Label>Intitulé du devis</Label>
                                <Input
                                    v-model="form.intitule"
                                    placeholder="Ex : Devis quantitatif – Bâtiment A"
                                />
                                <span
                                    v-if="form.errors.intitule"
                                    class="text-red-600 text-sm"
                                >
                                    {{ form.errors.intitule }}
                                </span>
                            </div>

                            <!-- Code -->
                            <div class="space-y-1">
                                <Label>Code du devis</Label>
                                <Input
                                    v-model="form.code"
                                    placeholder="DQ-2026-001"
                                />
                                <span
                                    v-if="form.errors.code"
                                    class="text-red-600 text-sm"
                                >
                                    {{ form.errors.code }}
                                </span>
                            </div>

                            <!-- Template -->
                            <!-- <div class="flex items-center gap-2">
                                <Checkbox
                                    v-model:checked="form.is_template"
                                />
                                <Label>Enregistrer comme template</Label>
                            </div> -->

                            <!-- Actions -->
                            <div class="flex justify-end mt-6">
                                <Button
                                    :disabled="form.processing"
                                    class="bg-blue-600 hover:bg-blue-700"
                                >
                                    <CheckCircle class="w-4 h-4 mr-1" />
                                    Créer et continuer
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>
