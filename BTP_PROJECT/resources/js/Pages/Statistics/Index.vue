<script setup>
import { ref, reactive, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

import AdvancedFilters from '@/components/AdvancedFilters.vue'

import axios from 'axios'

// Props
const props = defineProps({
  communes: Array,
  arrondissements: Array,
  categories: Array,
  materiaux: Array,
  devises: Array,
  users: Array,
})

// Données filtrées
const statsData = ref([])

// Filtres globaux
const filters = reactive({})

// Écoute des filtres
const updateFilters = (newFilters) => {
  Object.assign(filters, newFilters)
  fetchData()
}

// Récupération des données via API
const fetchData = async () => {
    console.log('gfyfyfyyf');
    
  const { data } = await axios.get(route('filter.data'), { params: filters })
  statsData.value = data
}
</script>

<template>
  <SidebarProvider>
    <AdminLayout>
      <Head title="Statistiques Avancées" />

      <PageBreadcrumb :pageTitle="'Statistiques'" />

      <!-- SECTION FILTRES AVANCÉS -->
      <AdvancedFilters
        :communes="props.communes"
        :arrondissements="props.arrondissements"
        :categories="props.categories"
        :materiaux="props.materiaux"
        :devises="props.devises"
        :users="props.users"
        @update="updateFilters"
      />

      <!-- SECTION GRAPHIQUES -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
        <Card>
          <CardHeader>
            <CardTitle>Graphique des prix par catégorie</CardTitle>
          </CardHeader>
          <CardContent>
            <!-- Ici, on pourra brancher un chart type Chart.js ou ApexCharts -->
            <pre>{{ statsData }}</pre>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Graphique des prix par commune</CardTitle>
          </CardHeader>
          <CardContent>
            <pre>{{ statsData }}</pre>
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  </SidebarProvider>
</template>
