<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    id: Number,
})

defineEmits(['delete'])
</script>

<template>
    <div class="flex justify-end gap-3">
        <Link :href="`/bordereaux/${id}`" class="text-blue-600">
            Voir
        </Link>

        <Link :href="`/bordereaux/${id}/edit`" class="text-yellow-600">
            Modifier
        </Link>

        <button
            @click="$emit('delete')"
            class="text-red-600"
        >
            Supprimer
        </button>
    </div>
</template>
