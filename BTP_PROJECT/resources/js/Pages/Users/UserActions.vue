<script setup lang="ts">
import { MoreHorizontal } from 'lucide-vue-next'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Button } from '@/components/ui/button'
import { router } from '@inertiajs/vue3'

defineProps<{
  user: {
    id: number
  }
}>()

function editUser(id: number) {
  router.visit(`/users/${id}/edit`)
}

function deleteUser(id: number) {
  if (confirm('Are you sure?')) {
    router.delete(`/users/${id}`)
  }
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon">
        <MoreHorizontal class="h-4 w-4" />
      </Button>
    </DropdownMenuTrigger>

    <DropdownMenuContent align="end">
      <DropdownMenuItem @click="editUser(user.id)">
        Edit
      </DropdownMenuItem>
      <DropdownMenuItem
        class="text-destructive"
        @click="deleteUser(user.id)"
      >
        Delete
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
