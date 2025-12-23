<template>
    <SidebarProvider>
        <admin-layout>
            <div class="grid grid-cols-12 gap-4 md:gap-6">
                {{ $page.props.auth.user.name }}
                  <Button>Click me</Button> 
               <Switch />

            </div>
        </admin-layout></SidebarProvider
    >
</template>

<script setup lang="ts">
import AdminLayout from "../components/layout/AdminLayout.vue";
import SidebarProvider from "../components/layout/SidebarProvider.vue";
import  {Switch}  from '../components/ui/switch'
import  {Button}  from '@/components/ui/button'

</script>
