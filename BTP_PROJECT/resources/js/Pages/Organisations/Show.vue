<template>
    <Head :title="organisation.name" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="space-y-6">
                <!-- En-tête -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-16 h-16 flex items-center justify-center rounded-xl bg-blue-100 text-blue-600"
                            >
                                <img
                                    v-if="organisation.logo"
                                    :src="`/storage/${organisation.logo}`"
                                    alt="Logo organisation"
                                    class="w-14 h-14 object-contain"
                                />
                                <Building2 v-else class="w-8 h-8" />
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold">
                                    {{ organisation.name }}
                                </h1>
                                <p class="text-sm text-gray-500">
                                    {{ organisation.raison_sociale }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    Créée par {{ organisation.user?.name ?? '—' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <Link
                                :href="route('organisations.edit', organisation.id)"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm"
                            >
                                <Edit class="w-4 h-4 mr-2" />
                                Modifier
                            </Link>

                            <Link
                                :href="route('organisations.index')"
                                class="inline-flex items-center px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50"
                            >
                                <ArrowLeft class="w-4 h-4 mr-2" />
                                Retour
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Informations générales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-2xl border bg-white p-5 shadow-sm">
                        <h3 class="text-lg font-semibold mb-4">
                            Informations générales
                        </h3>

                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Adresse</dt>
                                <dd class="font-medium">
                                    {{ organisation.adresse ?? '—' }}
                                </dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-gray-500">Pays</dt>
                                <dd class="font-medium">
                                    {{ organisation.pays ?? '—' }}
                                </dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-gray-500">Devise</dt>
                                <dd class="font-medium">
                                    {{ organisation.devise ?? '—' }}
                                </dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-gray-500">Date de création</dt>
                                <dd class="font-medium">
                                    {{ new Date(organisation.created_at).toLocaleDateString() }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Statistiques -->
                    <div class="rounded-2xl border bg-white p-5 shadow-sm">
                        <h3 class="text-lg font-semibold mb-4">
                            Aperçu
                        </h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="rounded-lg border p-4 text-center">
                                <p class="text-2xl font-bold">
                                    {{ organisation.clients?.length ?? 0 }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Clients
                                </p>
                            </div>

                            <div class="rounded-lg border p-4 text-center">
                                <p class="text-2xl font-bold">
                                    {{ organisation.projets?.length ?? 0 }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Projets
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clients -->
                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4">
                        Clients
                    </h3>

                    <div v-if="organisation.clients?.length">
                        <ul class="divide-y">
                            <li
                                v-for="client in organisation.clients"
                                :key="client.id"
                                class="py-3 flex justify-between"
                            >
                                <span class="font-medium">
                                    {{ client.nom ?? client.raison_sociale }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    {{ client.email ?? '—' }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <p v-else class="text-sm text-gray-500">
                        Aucun client associé
                    </p>
                </div>

                <!-- Projets -->
                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4">
                        Projets
                    </h3>

                    <div v-if="organisation.projets?.length">
                        <ul class="divide-y">
                            <li
                                v-for="projet in organisation.projets"
                                :key="projet.id"
                                class="py-3 flex justify-between"
                            >
                                <span class="font-medium">
                                    {{ projet.nom }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    {{ projet.statut ?? '—' }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <p v-else class="text-sm text-gray-500">
                        Aucun projet associé
                    </p>
                </div>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { Building2, Edit, ArrowLeft } from 'lucide-vue-next'

import AdminLayout from '@/components/layout/AdminLayout.vue'
import SidebarProvider from '@/components/layout/SidebarProvider.vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'

const props = defineProps({
    organisation: {
        type: Object,
        required: true
    }
})

const currentPageTitle = ref('Détails de l' + 'organisation')
</script>
