<template>
  <ThemeProvider>
      <div class="min-h-screen xl:flex">
        <AppSidebar />
        <Backdrop />

        <div
          class="flex-1 transition-all duration-300 ease-in-out"
          :class="isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]'"
        >
          <AppHeader />
          <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
            <slot />
          </div>
        </div>
      </div>
  </ThemeProvider>
</template>


<script setup>
import AppSidebar from './AppSidebar.vue'
import AppHeader from './AppHeader.vue'
import { useSidebar } from '../../composables/useSidebar'
import Backdrop from './Backdrop.vue'
import ThemeProvider from './ThemeProvider.vue'
const { isExpanded, isHovered } = useSidebar()

</script>
