<script setup lang="ts">
import { ref, computed } from "vue"
import { Head, router, usePage } from "@inertiajs/vue3"

import AdminLayout from "@/components/layout/AdminLayout.vue"
import SidebarProvider from "@/components/layout/SidebarProvider.vue"
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue"

import {
    Table,
    TableHeader,
    TableRow,
    TableHead,
    TableCell,
    TableBody,
} from "@/components/ui/table"

import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"

import {
    AlertDialog,
    AlertDialogTrigger,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogCancel,
    AlertDialogAction,
} from "@/components/ui/alert-dialog"

import { TrashIcon, PlusIcon } from "lucide-vue-next"

const props = defineProps<{
    collectors: Array<{
        id: number
        name: string
        email: string
        created_at: string
    }>
}>()

const page = usePage()

/**
 * Permissions envoyées globalement par Inertia
 * ex: auth.permissions = ['SYSTEM_COLLECTOR_VIEW', ...]
 */
const permissions = computed<string[]>(() => {
    return (page.props.auth as any)?.permissions ?? []
})

const canCreate = computed(() =>
    permissions.value.includes("SYSTEM_COLLECTOR_CREATE")
)

const canDelete = computed(() =>
    permissions.value.includes("SYSTEM_COLLECTOR_DELETE")
)

const currentPageTitle = ref("Collecteurs")

const goToCreate = () => {
    router.visit(route("collectors.create"))
}

const deleteCollector = (collector: any) => {
    router.delete(route("collectors.destroy", collector.id))
}
</script>

<template>
    <Head title="Collecteurs" />

    <SidebarProvider>
        <AdminLayout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />

            <div class="rounded-2xl border bg-white p-6 dark:bg-black">
                <!-- Bouton Ajouter -->
                <div class="flex justify-end mb-4" v-if="canCreate">
                    <Button variant="outline" @click="goToCreate">
                        Ajouter un collecteur
                        <PlusIcon class="w-4 h-4 ml-2 text-blue-600" />
                    </Button>
                </div>

                <!-- Aucun collecteur -->
                <div v-if="props.collectors.length === 0" class="text-center py-10">
                    <p class="text-sm text-muted-foreground">
                        Aucun collecteur trouvé pour cette organisation.
                    </p>
                </div>

                <!-- Tableau -->
                <Table v-else>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Collecteur</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Date d’ajout</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow
                            v-for="collector in props.collectors"
                            :key="collector.id"
                        >
                            <TableCell>
                                <span class="font-medium">
                                    {{ collector.name }}
                                </span>
                            </TableCell>

                            <TableCell>
                                {{ collector.email }}
                            </TableCell>

                            <TableCell>
                                <Badge variant="outline">
                                    {{ collector.created_at }}
                                </Badge>
                            </TableCell>

                            <!-- Actions -->
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <AlertDialog v-if="canDelete">
                                        <AlertDialogTrigger as-child>
                                            <Button size="sm" variant="secondary">
                                                <TrashIcon class="h-4 w-4 text-red-600" />
                                            </Button>
                                        </AlertDialogTrigger>

                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>
                                                    Supprimer le collecteur
                                                </AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    Êtes-vous sûr de vouloir retirer
                                                    <strong>{{ collector.name }}</strong>
                                                    de cette organisation ?
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>

                                            <AlertDialogFooter>
                                                <AlertDialogCancel>
                                                    Annuler
                                                </AlertDialogCancel>
                                                <AlertDialogAction
                                                    @click="deleteCollector(collector)"
                                                    class="bg-red-600 text-white hover:bg-red-700"
                                                >
                                                    Supprimer
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </AdminLayout>
    </SidebarProvider>
</template>
