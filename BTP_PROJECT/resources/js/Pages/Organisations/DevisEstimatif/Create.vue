<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

// Icônes Lucide
import { PlusCircle, Trash2, Plus, XCircle, CheckCircle, Calculator, Sigma } from 'lucide-vue-next';

// Types explicites
interface Niveau {
  id: number;
  nom: string;
}

interface Unite {
  id: number;
  nom: string;
}

const props = defineProps<{
  niveaux: Niveau[];
  unites: Unite[];
}>();

const form = useForm({
  intitule: '',
  niveaux: [
    {
      niveau_id: null as number | null,
      composants: [
        {
          code: '',
          piece: '',
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
        code: '',
        piece: '',
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
    code: '',
    piece: '',
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
    .map(n => n.niveau_id)
    .filter((id): id is number => id !== null);
  return props.niveaux.filter(n => !selectedIds.includes(n.id));
};

// Sous-totaux
const niveauSubtotal = (niveau: typeof form.niveaux[number]) => {
  return niveau.composants.reduce((sum, comp) => {
    return sum + (comp.qte * comp.prix_unitaire);
  }, 0);
};

// Total général
const totalGeneral = computed(() => {
  return form.niveaux.reduce((sum, niveau) => {
    return sum + niveauSubtotal(niveau);
  }, 0);
});

const submit = () => {
  form.post(route('devis_estmatif.store'));
};
</script>

<template>
  <Head title="Créer un Devis Estimatif" />

  <div class="p-6 space-y-6">
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <Calculator class="w-5 h-5 text-blue-600" />
          Créer un Devis Estimatif
        </CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">

        <!-- Intitulé -->
        <div class="space-y-1">
          <Label for="intitule">Intitulé</Label>
          <Input id="intitule" v-model="form.intitule" placeholder="Nom du devis" />
        </div>

        <!-- Niveaux -->
        <div v-for="(niveau, nIndex) in form.niveaux" :key="nIndex" class="border p-4 rounded space-y-4 bg-gray-50">
          
          <div class="flex justify-between items-center">
            <Label class="font-semibold">Niveau</Label>
            <Button size="sm" variant="destructive" @click="removeNiveau(nIndex)">
              <Trash2 class="w-4 h-4 mr-1" /> Supprimer Niveau
            </Button>
          </div>

          <Select v-model="niveau.niveau_id">
            <SelectTrigger class="w-full">
              <SelectValue placeholder="-- Choisir un niveau --" />
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

          <!-- Composants -->
          <div v-for="(comp, cIndex) in niveau.composants" :key="cIndex" class="grid grid-cols-7 gap-2 items-end">
            <div>
              <Label>Code</Label>
              <Input v-model="comp.code" placeholder="ASxx" />
            </div>
            <div class="col-span-2">
              <Label>Pièce</Label>
              <Input v-model="comp.piece" placeholder="Nom de la pièce" />
            </div>
            <div>
              <Label>Unité</Label>
              <Select v-model="comp.unite_id">
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="-- Unité --" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="u in props.unites" :key="u.id" :value="u.id">{{ u.nom }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div>
              <Label>Qté</Label>
              <Input type="number" v-model.number="comp.qte" placeholder="0" />
            </div>
            <div>
              <Label>Prix Unitaire</Label>
              <Input type="number" v-model.number="comp.prix_unitaire" placeholder="0" />
            </div>

            <!-- Bouton sur la même ligne -->
            <div class="flex items-center justify-center">
              <Button size="sm" variant="destructive" @click="removeComposant(nIndex, cIndex)">
                <XCircle class="w-4 h-4 mr-1" /> Supprimer
              </Button>
            </div>
          </div>

          <Button size="sm" variant="outline" @click="addComposant(nIndex)">
            <Plus class="w-4 h-4 mr-1" /> Ajouter un composant
          </Button>

          <!-- Sous-total -->
          <div class="flex justify-end items-center bg-white p-2 rounded mt-2 shadow-sm">
            <Calculator class="w-4 h-4 mr-2 text-gray-600" />
            <span class="font-semibold">Sous-total : {{ niveauSubtotal(niveau).toLocaleString() }} FCFA</span>
          </div>
        </div>

        <Button size="sm" variant="secondary" @click="addNiveau">
          <PlusCircle class="w-4 h-4 mr-1" /> Ajouter un niveau
        </Button>

        <!-- Total général -->
        <div class="flex justify-end items-center text-green-600 font-bold text-lg mt-6">
          <Sigma class="w-5 h-5 mr-2" />
          Total Général : {{ totalGeneral.toLocaleString() }} FCFA
        </div>

        <div class="mt-4 flex justify-end">
          <Button @click="submit" class="bg-blue-600 text-white hover:bg-blue-700">
            <CheckCircle class="w-4 h-4 mr-1" /> Créer Devis
          </Button>
        </div>

      </CardContent>
    </Card>
  </div>
</template>
