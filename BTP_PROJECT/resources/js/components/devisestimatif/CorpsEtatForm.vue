<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Plus, Trash } from "lucide-vue-next";
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem,
} from "@/components/ui/select";

const props = defineProps<{
    corpsEtat: any;
    devis: any;
    unites: any[];
    initialLots: any[];
}>();

const form = useForm({
    lots: JSON.parse(JSON.stringify(props.initialLots ?? [])),
});

const addLot = () => {
    form.lots.push({
        code: "",
        intitule: "",
        composants: [],
    });
};

const addComposant = (lotIndex: number) => {
    form.lots[lotIndex].composants.push({
        designation: "",
        unite_id: null,
        quantite: 0,
        prix_unitaire: 0,
    });
};

const removeLot = (index: number) => form.lots.splice(index, 1);
const removeComposant = (l: number, c: number) =>
    form.lots[l].composants.splice(c, 1);

const submit = () => {
    form.put(
        route("devis-quantitatif.update-corps-etat", {
            devis: props.devis.id,
            corpsEtat: props.corpsEtat.id,
        })
    );
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div
            v-for="(lot, lIndex) in form.lots"
            :key="lIndex"
            class="border rounded-lg p-4 space-y-4"
        >
            <div class="flex gap-2">
                <Input v-model="lot.code" placeholder="Code lot" />
                <Input v-model="lot.intitule" placeholder="Intitulé" />
                <Button variant="destructive" @click="removeLot(lIndex)">
                    <Trash class="w-4 h-4" />
                </Button>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th>Désignation</th>
                        <th>Unité</th>
                        <th>Qté</th>
                        <th>PU</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(c, cIndex) in lot.composants" :key="cIndex">
                        <td><Input v-model="c.designation" /></td>
                        <td>
                            <Select v-model="c.unite_id">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="u in unites"
                                        :key="u.id"
                                        :value="u.id"
                                    >
                                        {{ u.libelle }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </td>
                        <td><Input type="number" v-model.number="c.quantite" /></td>
                        <td><Input type="number" v-model.number="c.prix_unitaire" /></td>
                        <td>
                            <Button
                                variant="ghost"
                                @click="removeComposant(lIndex, cIndex)"
                            >
                                <Trash class="w-4 h-4" />
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <Button variant="secondary" @click="addComposant(lIndex)">
                <Plus class="w-4 h-4 mr-1" /> Ajouter composant
            </Button>
        </div>

        <Button variant="outline" @click="addLot">
            <Plus class="w-4 h-4 mr-1" /> Ajouter un lot
        </Button>

        <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
                Enregistrer ce corps d’état
            </Button>
        </div>
    </form>
</template>
