<script setup>
import { reactive, watch } from 'vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

const props = defineProps({
  communes: Array,
  arrondissements: Array,
  categories: Array,
  materiaux: Array,
  devises: Array,
  users: Array,
})

const localFilters = reactive({
  commune_ids: [],
  arrondissement_ids: [],
  categorie_ids: [],
  materiau_ids: [],
  user_ids: [],
  devise_id: null,
  point_vente: '',
  quartier: '',
  status: null,
  date_from: null,
  date_to: null,
})

// Émettre les filtres à chaque changement
const emit = defineEmits(['update'])
watch(localFilters, () => emit('update', localFilters), { deep: true })
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Filtres Avancés</CardTitle>
    </CardHeader>
    <CardContent class="grid grid-cols-1 lg:grid-cols-3 gap-4">

      <!-- Commune -->
      <div>
        <Label>Commune</Label>
        <Select v-model="localFilters.commune_ids" multiple>
          <SelectTrigger class="w-full">
            <SelectValue placeholder="-- Choisir une commune --" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="c in props.communes" :key="c.id" :value="c.id">
              {{ c.libelle }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Arrondissement -->
      <div>
        <Label>Arrondissement</Label>
        <Select v-model="localFilters.arrondissement_ids" multiple>
          <SelectTrigger class="w-full">
            <SelectValue placeholder="-- Choisir un arrondissement --" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="a in props.arrondissements" :key="a.id" :value="a.id">
              {{ a.libelle }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Catégorie -->
      <div>
        <Label>Catégorie</Label>
        <Select v-model="localFilters.categorie_ids" multiple>
          <SelectTrigger class="w-full">
            <SelectValue placeholder="-- Choisir une catégorie --" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="cat in props.categories" :key="cat.id" :value="cat.id">
              {{ cat.nom }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Matériaux -->
      <div>
        <Label>Matériaux</Label>
        <Select v-model="localFilters.materiau_ids" multiple>
          <SelectTrigger class="w-full">
            <SelectValue placeholder="-- Choisir un matériau --" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="m in props.materiaux" :key="m.id" :value="m.id">
              {{ m.nom }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Utilisateur / Collecteur -->
      <div>
        <Label>Collecteur</Label>
        <Select v-model="localFilters.user_ids" multiple>
          <SelectTrigger class="w-full">
            <SelectValue placeholder="-- Choisir un collecteur --" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="u in props.users" :key="u.id" :value="u.id">
              {{ u.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Devise -->
      <div>
        <Label>Devise</Label>
        <Select v-model="localFilters.devise_id">
          <SelectTrigger class="w-full">
            <SelectValue placeholder="-- Choisir une devise --" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="d in props.devises" :key="d.id" :value="d.id">
              {{ d.code }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Status -->
      <div>
        <Label>Status</Label>
        <Select v-model="localFilters.status">
          <SelectTrigger class="w-full">
            <SelectValue placeholder="-- Tous / Actif / Inactif --" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem :value="null">Tous</SelectItem>
            <SelectItem :value="1">Actif</SelectItem>
            <SelectItem :value="0">Inactif</SelectItem>
          </SelectContent>
        </Select>
      </div>

      <!-- Quartier -->
      <div>
        <Label>Quartier</Label>
        <Input v-model="localFilters.quartier" placeholder="Nom du quartier" />
      </div>

      <!-- Point de vente -->
      <div>
        <Label>Point de vente</Label>
        <Input v-model="localFilters.point_vente" placeholder="Ex: Magasin X" />
      </div>

      <!-- Dates -->
      <div>
        <Label>Date début</Label>
        <Input type="date" v-model="localFilters.date_from" />
      </div>

      <div>
        <Label>Date fin</Label>
        <Input type="date" v-model="localFilters.date_to" />
      </div>

      <!-- Reset -->
      <div class="flex items-end">
        <Button variant="outline" @click="Object.keys(localFilters).forEach(k => localFilters[k] = Array.isArray(localFilters[k]) ? [] : null)">
          Réinitialiser
        </Button>
      </div>

    </CardContent>
  </Card>
</template>
