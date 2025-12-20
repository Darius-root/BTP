<template>
  <div class="relative" ref="dropdownRef">
    <button
      class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full h-11 w-11 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
      @click="toggleDropdown"
    >
      <span
        v-if="notifying"
        class="absolute right-0 top-0.5 z-10 h-2 w-2 rounded-full bg-orange-400"
      >
        <span
          class="absolute inline-flex w-full h-full bg-orange-400 rounded-full opacity-75 animate-ping"
        ></span>
      </span>

      <!-- Bell icon -->
      <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20">
        <path
          fill-rule="evenodd"
          clip-rule="evenodd"
          d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248Z"
        />
      </svg>
    </button>

    <!-- Dropdown -->
    <div
      v-if="dropdownOpen"
      class="absolute -right-[240px] mt-[17px] flex h-[480px] w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark sm:w-[361px] lg:right-0"
    >
      <div class="flex items-center justify-between pb-3 mb-3 border-b dark:border-gray-800">
        <h5 class="text-lg font-semibold text-gray-800 dark:text-white/90">
          Notifications
        </h5>

        <button @click="closeDropdown" class="text-gray-500 dark:text-gray-400">
          ✕
        </button>
      </div>

      <ul class="flex flex-col overflow-y-auto custom-scrollbar">
        <li
          v-for="notification in notifications"
          :key="notification.id"
          @click="handleItemClick"
        >
          <div
            class="flex gap-3 p-3 rounded-lg border-b border-gray-100 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-white/5 cursor-pointer"
          >
            <img
              :src="notification.userImage"
              class="w-10 h-10 rounded-full"
              alt="User"
            />

            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                <span class="font-medium text-gray-800 dark:text-white">
                  {{ notification.userName }}
                </span>
                {{ notification.action }}
                <span class="font-medium text-gray-800 dark:text-white">
                  {{ notification.project }}
                </span>
              </p>

              <p class="mt-1 text-xs text-gray-400">
                {{ notification.type }} · {{ notification.time }}
              </p>
            </div>
          </div>
        </li>
      </ul>

      <!-- View all -->
      <Link
        :href="'#'"
        class="mt-3 flex justify-center rounded-lg border border-gray-300 bg-white p-3 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
        @click="closeDropdown"
      >
        Voir toutes les notifications
      </Link>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'

const dropdownOpen = ref(false)
const notifying = ref(true)
const dropdownRef = ref(null)

const notifications = ref([
  {
    id: 1,
    userName: 'Terry Franci',
    userImage: '/images/user/user-02.jpg',
    action: 'demande une modification sur',
    project: 'Nganter App',
    type: 'Projet',
    time: '5 min',
  },
  {
    id: 2,
    userName: 'Terry Franci',
    userImage: '/images/user/user-03.jpg',
    action: 'a commenté',
    project: 'Nganter App',
    type: 'Projet',
    time: '10 min',
  },
])

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
  notifying.value = false
}

const closeDropdown = () => {
  dropdownOpen.value = false
}

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

const handleItemClick = () => {
  closeDropdown()
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
