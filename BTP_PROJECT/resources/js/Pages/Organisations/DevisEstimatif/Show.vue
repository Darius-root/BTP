<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'

import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

import {
  Card,
  CardHeader,
  CardTitle,
  CardContent
} from '@/components/ui/card'

import { Button } from '@/components/ui/button'

const props = defineProps({
  batiment: Object,
  devis: Object,
  totauxParNiveau: Array,
  totalGeneral: Number,
})

/**
 * Regrouper les composants par niveau
 */
const composantsParNiveau = computed(() => {
  const map = {}

  props.devis.composants.forEach(comp => {
    if (!map[comp.niveau.id]) {
      map[comp.niveau.id] = {
        niveau: comp.niveau,
        composants: [],
      }
    }

    map[comp.niveau.id].composants.push(comp)
  })

  return Object.values(map)
})
</script>
<template>
  <SidebarProvider>
    <AdminLayout>
      <Head :title="`Devis estimatif – ${devis.intitule}`" />

      <PageBreadcrumb :pageTitle="'Détail du devis estimatif'" />

      <!-- CONTEXTE -->
      <Card class="mb-6">
        <CardHeader>
          <CardTitle>{{ devis.intitule }}</CardTitle>
        </CardHeader>
        <CardContent class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
          <div>
            <strong>Organisation</strong><br />
            {{ devis.batiment.projet.organisation.nom }}
          </div>
          <div>
            <strong>Projet</strong><br />
            {{ devis.batiment.projet.nom }}
          </div>
          <div>
            <strong>Bâtiment</strong><br />
            {{ devis.batiment.nom }}
          </div>
          <div>
            <strong>Statut</strong><br />
            <span
              :class="[
                'px-2 py-1 rounded text-xs font-semibold',
                devis.statut === 'validé'
                  ? 'bg-green-100 text-green-700'
                  : 'bg-yellow-100 text-yellow-700'
              ]"
            >
              {{ devis.statut }}
            </span>
          </div>
        </CardContent>
      </Card>

      <!-- NIVEAUX -->
      <div
        v-for="bloc in composantsParNiveau"
        :key="bloc.niveau.id"
        class="mb-8"
      >
        <Card>
          <CardHeader>
            <CardTitle>{{ bloc.niveau.nom }}</CardTitle>
          </CardHeader>

          <CardContent class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200">
              <thead class="bg-gray-100">
                <tr>
                  <th class="border p-2">Code</th>
                  <th class="border p-2">Pièce</th>
                  <th class="border p-2">Unité</th>
                  <th class="border p-2 text-right">Qté</th>
                  <th class="border p-2 text-right">Prix unitaire</th>
                  <th class="border p-2 text-right">Montant</th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="comp in bloc.composants"
                  :key="comp.id"
                  class="hover:bg-gray-50"
                >
                  <td class="border p-2">{{ comp.code }}</td>
                  <td class="border p-2">{{ comp.piece }}</td>
                  <td class="border p-2">{{ comp.unite.nom }}</td>
                  <td class="border p-2 text-right">
                    {{ comp.qte }}
                  </td>
                  <td class="border p-2 text-right">
                    {{ comp.prix_unitaire.toLocaleString() }}
                  </td>
                  <td class="border p-2 text-right font-semibold">
                    {{ comp.montant.toLocaleString() }}
                  </td>
                </tr>
              </tbody>

              <tfoot>
                <tr class="bg-gray-50 font-bold">
                  <td colspan="5" class="border p-2 text-right">
                    Total {{ bloc.niveau.nom }}
                  </td>
                  <td class="border p-2 text-right">
                    {{
                      totauxParNiveau.find(
                        t => t.niveau_id === bloc.niveau.id
                      )?.total.toLocaleString()
                    }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </CardContent>
        </Card>
      </div>

      <!-- TOTAL GÉNÉRAL -->
      <Card>
        <CardContent class="flex justify-between items-center text-xl font-bold">
          <span>Total général</span>
          <span>{{ totalGeneral.toLocaleString() }} FCFA</span>
        </CardContent>
      </Card>

      <!-- ACTIONS -->
      <div class="mt-6 flex gap-3">
        <Button variant="secondary">Exporter PDF</Button>
        <Button variant="outline">Exporter Excel</Button>
        <Button
          v-if="devis.statut !== 'validé'"
          variant="default"
        >
          Valider le devis
        </Button>
      </div>

    </AdminLayout>
  </SidebarProvider>
</template>
