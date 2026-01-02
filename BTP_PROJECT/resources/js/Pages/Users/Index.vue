<script setup lang="ts">
import AdminLayout from "@/components/layout/AdminLayout.vue";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import { ref } from "vue";
const currentPageTitle = ref("Gestion des utilisateurs");

import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";

import {  Head} from "@inertiajs/vue3";

import { router } from "@inertiajs/vue3";
import DataTable from "@/components/DataTable.vue";
import { userColumns } from "./datatable/columns";

const props = defineProps<{
    users: any;
}>();

function onSearch({ value, columns }: { value: string; columns: string[] }) {
 
    router.get(
        "/users",
        {
            search: value,
            columns,
        },
        { preserveState: true, replace: true }
    );
}

function onPageChange(page: number) {
    router.get("/users", { page }, { preserveState: true });
}
</script>

<template>
    <Head title="Gestion des utilisateurs" />
    <SidebarProvider>
        <admin-layout>
            <PageBreadcrumb :pageTitle="currentPageTitle" />
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-black lg:p-6"
            >
                <h1 class="text-xl font-bold mb-4">Liste des utilisateurs</h1>

                <!-- DataTable générique -->
                <DataTable
                    :columns="userColumns"
                    :data="users.data"
                    :meta="users"
                    @search="onSearch"
                    @page-change="onPageChange"
                />
            </div>
        </admin-layout>
    </SidebarProvider>
</template>
