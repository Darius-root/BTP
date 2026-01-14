<script setup lang="ts">
import { ref, watch } from 'vue'
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
    status: boolean
    [key: string]: any
}

const props = defineProps<{
    item: BaseItem | null
    resource: string        // ex: "users", "collections-prix"
    label: string           // ex: "utilisateur", "prix", "produit"
    open: boolean
}>()

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
    (e: 'updated'): void
}>()

const actionType = ref<'activate' | 'deactivate'>('activate')

// recalculer l’action quand l’item change
watch(() => props.item, (val) => {
    if (val) {
        actionType.value = val.status ? 'deactivate' : 'activate'
    }
})

function confirmActivation() {
    if (!props.item) return

    const id = props.item.id
    const routeName = actionType.value === 'activate'
        ? `${props.resource}.activate`
        : `${props.resource}.deactivate`

    router.post(route(routeName, id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // mise à jour locale
            props.item!.status = actionType.value === 'activate'
            // reload pour synchroniser
            router.reload()
            emit('updated')
        }
    })

    emit('update:open', false)
}
</script>

<template>
    <AlertDialog :open="open" @update:open="emit('update:open', $event)">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>
                    {{ actionType === 'activate' ? `Activer ce ${label} ?` : `Désactiver ce ${label} ?` }}
                </AlertDialogTitle>
                <AlertDialogDescription class="space-y-3">
                    <p v-if="actionType === 'activate'">
                        Vous êtes sur le point d'activer ce {{ label }}.
                    </p>
                    <p v-else>
                        Vous êtes sur le point de désactiver ce {{ label }}.
                    </p>


                </AlertDialogDescription>
            </AlertDialogHeader>

            <AlertDialogFooter>
                <AlertDialogCancel>Annuler</AlertDialogCancel>
                <AlertDialogAction
                    :class="actionType === 'activate' ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white'"
                    @click="confirmActivation">
                    {{ actionType === 'activate' ? 'Activer' : 'Désactiver' }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
