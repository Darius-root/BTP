<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogCancel,
    AlertDialogAction
} from '@/components/ui/alert-dialog'

interface BaseItem {
    id: number
    [key: string]: any
}

const props = defineProps<{
    item: BaseItem | null
    resource: string        // ex: "users", "niveaux-batiment"
    label: string           // ex: "utilisateur", "niveau", "produit"
    open: boolean
}>()

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
    (e: 'deleted'): void
}>()

function confirmDelete() {
    if (!props.item) return

    const id = props.item.id
    const routeName = `${props.resource}.destroy`

    router.delete(route(routeName, id), {
        preserveScroll: true,
        onSuccess: () => {
            router.reload()
            emit('deleted')
        }
    })

    emit('update:open', false)
}
</script>

<template>
    <AlertDialog :open="open" @update:open="emit('update:open', $event)">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Supprimer ce {{ label }} ?</AlertDialogTitle>
                <AlertDialogDescription>
                    Vous êtes sur le point de supprimer
                    <strong>{{ item?.nom || label  }}</strong>.
                    <br />
                    Cette action est irréversible.
                </AlertDialogDescription>
            </AlertDialogHeader>

            <AlertDialogFooter>
                <AlertDialogCancel>Annuler</AlertDialogCancel>
                <AlertDialogAction class="bg-red-600 hover:bg-red-700 text-white" @click="confirmDelete">
                    Supprimer
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
