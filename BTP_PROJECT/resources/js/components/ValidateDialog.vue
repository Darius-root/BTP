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
    resource: string        // ex: "collections-prix"
    label: string           // ex: "collecte de prix"
    open: boolean
}>()

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void
    (e: 'validated'): void
}>()

const isValidating = ref(false)

function confirmValidate() {
    if (!props.item) return
    isValidating.value = true

    const id = props.item.id
    const routeName = `${props.resource}.validate`

    router.post(route(routeName, id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            emit('validated')
        },
        onFinish: () => {
            isValidating.value = false
        },
        onError: (errors) => {
            console.error(errors)
            alert('Une erreur est survenue lors de la validation.')
        }
    })

    emit('update:open', false)
}
</script>

<template>
    <AlertDialog :open="open" @update:open="emit('update:open', $event)">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Valider cette {{ label }} ?</AlertDialogTitle>
                <AlertDialogDescription>
                    Vous êtes sur le point de valider
                    <strong>{{ item?.nom || label }}</strong>.<br />
                    Une fois validée, cette collecte ne pourra plus être modifiée.
                </AlertDialogDescription>
            </AlertDialogHeader>

            <AlertDialogFooter>
                <AlertDialogCancel>Annuler</AlertDialogCancel>
                <AlertDialogAction class="bg-green-600 hover:bg-green-700 text-white" :disabled="isValidating"
                    @click="confirmValidate">
                    {{ isValidating ? 'Validation en cours...' : 'Valider' }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
