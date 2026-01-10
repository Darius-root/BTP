<script setup>
import { reactive, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    niveaux: Array,
    unites: Array,
});

const form = useForm({
    intitule: '',
    batiment_id: null,
    niveaux: [
        {
            niveau_id: null,
            composants: [
                {
                    code: '',
                    piece: '',
                    unite_id: null,
                    qte: 0,
                    prix_unitaire: 0,
                },
            ],
        },
    ],
});

const addNiveau = () => {
    form.niveaux.push({
        niveau_id: null,
        composants: [
            {
                code: '',
                piece: '',
                unite_id: null,
                qte: 0,
                prix_unitaire: 0,
            },
        ],
    });
};

const removeNiveau = (index) => {
    form.niveaux.splice(index, 1);
};

const addComposant = (niveauIndex) => {
    form.niveaux[niveauIndex].composants.push({
        code: '',
        piece: '',
        unite_id: null,
        qte: 0,
        prix_unitaire: 0,
    });
};

const removeComposant = (niveauIndex, compIndex) => {
    form.niveaux[niveauIndex].composants.splice(compIndex, 1);
};

const submit = () => {
    form.post(route('devis.store'));
};
</script>

<template>
    <Head title="Créer un Devis Estimatif" />

    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Créer un Devis Estimatif</h1>

        <div class="mb-4">
            <label class="block mb-1">Intitulé</label>
            <input v-model="form.intitule" class="border p-2 w-full" />
        </div>

        <div v-for="(niveau, nIndex) in form.niveaux" :key="nIndex" class="border p-4 mb-4 rounded">
            <div class="flex justify-between items-center mb-2">
                <label>Niveau</label>
                <button type="button" @click="removeNiveau(nIndex)" class="text-red-500">Supprimer Niveau</button>
            </div>

            <select v-model="niveau.niveau_id" class="border p-2 w-full mb-2">
                <option value="">-- Choisir un niveau --</option>
                <option v-for="n in niveaux" :key="n.id" :value="n.id">{{ n.nom }}</option>
            </select>

            <div v-for="(comp, cIndex) in niveau.composants" :key="cIndex" class="flex gap-2 mb-2 items-end">
                <input v-model="comp.code" placeholder="Code ASxx" class="border p-2 w-24" />
                <input v-model="comp.piece" placeholder="Pièce" class="border p-2 flex-1" />
                <select v-model="comp.unite_id" class="border p-2 w-32">
                    <option value="">-- Unité --</option>
                    <option v-for="u in unites" :key="u.id" :value="u.id">{{ u.nom }}</option>
                </select>
                <input v-model.number="comp.qte" placeholder="Qté" class="border p-2 w-24" type="number" />
                <input v-model.number="comp.prix_unitaire" placeholder="Prix Unitaire" class="border p-2 w-32" type="number" />
                <button type="button" @click="removeComposant(nIndex, cIndex)" class="text-red-500">Supprimer</button>
            </div>

            <button type="button" @click="addComposant(nIndex)" class="text-blue-500 mt-2">Ajouter un composant</button>
        </div>

        <button type="button" @click="addNiveau" class="text-green-500 mb-4">Ajouter un niveau</button>

        <div>
            <button @click="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Créer Devis</button>
        </div>
    </div>
</template>
