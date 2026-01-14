<template>
  <Link href="/" class="flex items-center space-x-3 lg:hidden">
    <div
      class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shadow-lg flex-shrink-0">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none"
        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" />
      </svg>
    </div>
    <div class="leading-tight">
      <div class="text-lg sm:text-xl font-extrabold text-gray-900 dark:text-white tracking-tight">
        BTP<span class="text-blue-600 dark:text-blue-400"> Bénin</span>
      </div>
      <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
        Gestion des Prix
      </div>
    </div>
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
