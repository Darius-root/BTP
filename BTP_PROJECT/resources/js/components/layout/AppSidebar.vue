<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

import {
    GridIcon,
    CalenderIcon,
    UserCircleIcon,
    PieChartIcon,
    ChevronDownIcon,
    HorizontalDots,
    ListIcon,
    PlugInIcon,
} from '../../icons'

import { useSidebar } from '../../composables/useSidebar'
import BoxCubeIcon from '@/icons/BoxCubeIcon.vue'

const page = usePage();

const { isExpanded, isMobileOpen, isHovered, openSubmenu } = useSidebar();

/**
 * Menu configuration
 */
const menuGroups = [
    {
        title: "Menu",
        items: [
            { icon: GridIcon, name: "Dashboard", path: "/dashboard" },
            {
                name: 'Profil',
                icon: UserCircleIcon,
                path: '/profile',
            },
            {
                name: 'Dashboard',
                icon: GridIcon,
                path: '/dashboard',
            },
            {
                name: 'Bordereaux',
                icon: ListIcon,
                path: '/bordereaux',
            },
            {
                name: "Organisations",
                icon: ListIcon,
                subItems: [
                    { name: "Liste", path: "/organisations" },
                    { name: "Utilisateurs", path: "/organisations/users" },
                    { name: "Rôles", path: "/organisations/roles" },
                ]

            },

            {
                name: 'Clients',
                icon: UserCircleIcon,
                path: '/clients',
            },
            {
                name: 'Projets',
                icon: BoxCubeIcon,
                path: '/projets',
            },


            {
                name: 'Collections de prix',
                icon: ListIcon,
                path: '/collections-prix',
            },
        ],
    },
    {
        title: "Fonctions",
        items: [

            {
                name: "Configurations",
                icon: ListIcon,
                subItems: [
                    { name: "Rôles", path: "/roles" },
                    { name: "Permissions", path: "/permissions" },
                    { name: 'Communes', path: '/communes' },
                    { name: 'Arrondissements', path: '/arrondissements' },
                    { name: 'Unités de mesure', path: '/unites-mesure' },
                    { name: 'Matériaux', path: '/materiaux' },
                    { name: 'Devises', path: '/devises' },
                    { name: 'Corps d\'état', path: '/corps-etat' },
                ],
            },
        ],
    },

    {
        title: "Utilisateurs",
        items: [

            {
                name: "Utilisateurs",
                icon: ListIcon,
                subItems: [
                    { name: "Liste", path: "/users" },
                    { name: "Rôles", path: "/permissions" },
                ],
            },
        ],
    },
];

const isActive = (path) => page.url === path;

const toggleSubmenu = (groupIndex, itemIndex) => {
    const key = `${groupIndex}-${itemIndex}`;
    openSubmenu.value = openSubmenu.value === key ? null : key;
};

const isSubmenuOpen = (groupIndex, itemIndex) =>
    openSubmenu.value === `${groupIndex}-${itemIndex}`;
</script>

<template>
    <aside :class="[
        'fixed mt-16 flex flex-col lg:mt-0 top-0 px-5 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-50 border-r border-gray-200',
        {
            'lg:w-[290px]': isExpanded || isHovered || isMobileOpen,
            'lg:w-[90px]': !isExpanded && !isHovered,
            'translate-x-0 w-[290px]': isMobileOpen,
            '-translate-x-full': !isMobileOpen,
            'lg:translate-x-0': true,
        },
    ]" @mouseenter="!isExpanded && (isHovered = true)" @mouseleave="isHovered = false">
        <!-- Logo avec composant BTP Bénin -->
        <div class="py-6">
            <!-- Composant BTP Bénin complet (visible quand la sidebar est étendue) -->
            <div v-if="isExpanded || isHovered || isMobileOpen" class="flex justify-center mb-6">
                <Link href="/" class="flex items-center space-x-3">
                    <!-- Icône -->
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" />
                        </svg>
                    </div>

                    <!-- Texte -->
                    <div class="leading-tight">
                        <div class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                            BTP<span class="text-blue-600 dark:text-blue-400"> Bénin</span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                            Gestion des Prix
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Icône seule (visible quand la sidebar est réduite) -->
            <div v-else class="flex justify-center">
                <Link href="/">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" />
                        </svg>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Menu -->
        <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
            <nav class="mb-6">
                <div class="flex flex-col gap-4">
                    <div v-for="(menuGroup, groupIndex) in menuGroups" :key="groupIndex">
                        <h2 :class="[
                            'mb-4 text-xs uppercase flex leading-[20px] text-gray-400 dark:text-gray-500',
                            !isExpanded && !isHovered
                                ? 'lg:justify-center'
                                : 'justify-start',
                        ]">
                            <template v-if="isExpanded || isHovered || isMobileOpen">
                                {{ menuGroup.title }}
                            </template>
                            <HorizontalDots v-else />
                        </h2>

                        <ul class="flex flex-col gap-4">
                            <li v-for="(item, index) in menuGroup.items" :key="item.name">
                                <!-- Item with Submenu -->
                                <button v-if="item.subItems" @click="toggleSubmenu(groupIndex, index)" :class="[
                                    'menu-item group w-full',
                                    isSubmenuOpen(groupIndex, index)
                                        ? 'menu-item-active'
                                        : 'menu-item-inactive',
                                    !isExpanded && !isHovered
                                        ? 'lg:justify-center'
                                        : 'lg:justify-start',
                                ]">
                                    <span :class="isSubmenuOpen(groupIndex, index)
                                            ? 'menu-item-icon-active'
                                            : 'menu-item-icon-inactive'
                                        ">
                                        <component :is="item.icon" />
                                    </span>
                                    <span v-if="
                                        isExpanded ||
                                        isHovered ||
                                        isMobileOpen
                                    " class="menu-item-text dark:text-gray-300">
                                        {{ item.name }}
                                    </span>
                                    <ChevronDownIcon v-if="
                                        isExpanded ||
                                        isHovered ||
                                        isMobileOpen
                                    " :class="[
                                            'ml-auto w-5 h-5 transition-transform duration-200',
                                            {
                                                'rotate-180 text-brand-500 dark:text-brand-400':
                                                    isSubmenuOpen(
                                                        groupIndex,
                                                        index
                                                    ),
                                            },
                                        ]" />
                                </button>

                                <!-- Item without Submenu -->
                                <Link v-else-if="item.path" :href="item.path" :class="[
                                    'menu-item group',
                                    isActive(item.path)
                                        ? 'menu-item-active'
                                        : 'menu-item-inactive',
                                ]">
                                    <span :class="isActive(item.path)
                                            ? 'menu-item-icon-active'
                                            : 'menu-item-icon-inactive'
                                        ">
                                        <component :is="item.icon" />
                                    </span>
                                    <span v-if="
                                        isExpanded ||
                                        isHovered ||
                                        isMobileOpen
                                    " class="menu-item-text dark:text-gray-300">
                                        {{ item.name }}
                                    </span>
                                </Link>

                                <!-- Submenu -->
                                <transition>
                                    <div v-show="isSubmenuOpen(groupIndex, index) &&
                                        (isExpanded ||
                                            isHovered ||
                                            isMobileOpen)
                                        ">
                                        <ul class="mt-2 space-y-1 ml-9">
                                            <li v-for="subItem in item.subItems" :key="subItem.name">
                                                <Link v-if="subItem.path" :href="subItem.path" :class="[
                                                    'menu-dropdown-item dark:text-gray-300',
                                                    isActive(subItem.path)
                                                        ? 'menu-dropdown-item-active'
                                                        : 'menu-dropdown-item-inactive',
                                                ]">
                                                    {{ subItem.name }}
                                                    <span class="flex items-center gap-1 ml-auto">
                                                        <span v-if="subItem.new" class="menu-dropdown-badge">new</span>
                                                        <span v-if="subItem.pro" class="menu-dropdown-badge">pro</span>
                                                    </span>
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>
                                </transition>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </aside>
</template>