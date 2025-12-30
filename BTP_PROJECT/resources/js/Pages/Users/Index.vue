<script setup lang="ts">
import DataTable from "@/components/DataTable.vue"
import { userColumns } from "./columns"
import { usePage } from "@inertiajs/vue3"

const users = usePage().props.users
</script>



<template>
  <div>
    <h1 class="text-xl font-bold mb-4">Liste des utilisateurs</h1>
    <DataTable :columns="userColumns" :data="users" />
  </div>
</template>
