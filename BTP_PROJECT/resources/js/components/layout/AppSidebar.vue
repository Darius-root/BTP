<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

import {
    GridIcon,
    UserCircleIcon,
    ChevronDownIcon,
    HorizontalDots,
    ListIcon,
} from "../../icons";

import { useSidebar } from "../../composables/useSidebar";
import BoxCubeIcon from "@/icons/BoxCubeIcon.vue";

/**
 * PAGE PROPS (auth & permissions)
 */
const page = usePage();

/**
 * Vérifie si l'utilisateur a la permission
 */
const can = (permission) => {
    return !permission || page.props.auth?.permissions?.includes(permission);
};

/**
 * Sidebar state
 */
const { isExpanded, isMobileOpen, isHovered, openSubmenu } = useSidebar();

/**
 * ===============================
 * MENU CONFIGURATION
 * ===============================
 */
const menuGroups = [
    {
        title: "Menu",
        items: [
            { name: "Dashboard", icon: GridIcon, path: "/dashboard" },
            { name: "Profil", icon: UserCircleIcon, path: "/profile" },
            {
                name: "Organisations",
                icon: ListIcon,
                subItems: [
                    { name: "Mes organisations", path: "/organisations" },
                    { name: "Utilisateurs", path: "/organisations/users", permission: "ORG_ORGANISATIONUSER_VIEW" },
                    { name: "Rôles", path: "/organisations/roles", permission: "ORG_ORGANISATION_ROLE_VIEW" },
                ],
            },
            { name: "Clients", icon: UserCircleIcon, path: "/clients", permission: "ORG_CLIENT_VIEW" },
            { name: "Projets", icon: BoxCubeIcon, path: "/projets", permission: "ORG_PROJET_VIEW" },
            { name: "Collections de prix", icon: ListIcon, path: "/collections-prix" },
        ],
    },
    {
        title: "Fonctions",
        items: [
            {
                name: "Configurations",
                icon: ListIcon,
                subItems: [
                    { name: "Niveau Batiment", path: "/niveaux-batiment", permission: "SYSTEM_NIVEAU_BATIMENT_VIEW" },
                    { name: "Corps d'état", path: "/corps-etat", permission: "SYSTEM_CORPS_ETAT_VIEW" },
                    { name: "Devises", path: "/devises", permission: "SYSTEM_DEVISE_VIEW" },
                    { name: "Matériaux", path: "/materiaux", permission: "SYSTEM_MATERIAU_VIEW" },
                    { name: "Unités de mesure", path: "/unites-mesure", permission: "SYSTEM_UNITE_MESURE_VIEW" },
                    { name: "Communes", path: "/communes", permission: "SYSTEM_COMMUNE_VIEW" },
                    { name: "Arrondissements", path: "/arrondissements", permission: "SYSTEM_ARRONDISSEMENT_VIEW" },
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
                    { name: "Utilisateurs", path: "/users", permission: "SYSTEM_USER_VIEW" },
                    { name: "Permissions", path: "/permissions", permission: "SYSTEM_PERMISSION_VIEW" },
                    { name: "Rôles", path: "/roles", permission: "SYSTEM_ROLE_VIEW" },
                ],
            },
        ],
    },
];

/**
 * ===============================
 * FILTER MENUS BY PERMISSIONS
 * ===============================
 */
const filteredMenuGroups = computed(() => {
    return menuGroups
        .map((group) => {
            const items = group.items
                .map((item) => {
                    // Filtrer les sous-items
                    if (item.subItems) {
                        const subItems = item.subItems.filter(sub => can(sub.permission));
                        if (!subItems.length) return null;
                        return { ...item, subItems };
                    }
                    // Filtrer l'item simple
                    if (item.permission && !can(item.permission)) return null;
                    return item;
                })
                .filter(Boolean);

            return items.length ? { ...group, items } : null;
        })
        .filter(Boolean);
});

/**
 * ===============================
 * UI HELPERS
 * ===============================
 */
const isActive = (path) => page.url === path;

const toggleSubmenu = (groupIndex, itemIndex) => {
    const key = `${groupIndex}-${itemIndex}`;
    openSubmenu.value = openSubmenu.value === key ? null : key;
};

const isSubmenuOpen = (groupIndex, itemIndex) =>
    openSubmenu.value === `${groupIndex}-${itemIndex}`;

// Helper pour déterminer si le logo complet doit être affiché
const shouldShowFullLogo = computed(() => {
    return isExpanded || isHovered || isMobileOpen;
});
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
        <!-- LOGO SECTION -->
        <div class="py-6 flex-shrink-0">
            <!-- Logo complet (visible quand la sidebar est étendue) -->
            <div v-if="shouldShowFullLogo" class="flex justify-center">
                <Link href="/" class="flex items-center space-x-3 transition-all duration-300">
                    <!-- Icône du logo -->
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shadow-lg flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" />
                        </svg>
                    </div>

                    <!-- Texte du logo -->
                    <div class="leading-tight overflow-hidden transition-all duration-300">
                        <div
                            class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight whitespace-nowrap">
                            BTP<span class="text-blue-600 dark:text-blue-400"> Bénin</span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium whitespace-nowrap">
                            Gestion des Prix
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Icône seule (visible quand la sidebar est réduite) -->
            <div v-else class="flex justify-center">
                <Link href="/" class="flex items-center justify-center">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" />
                        </svg>
                    </div>
                </Link>
            </div>
        </div>

        <!-- MENU -->
        <nav class="flex-1 overflow-y-auto duration-300 ease-linear no-scrollbar">
            <div class="flex flex-col gap-4">
                <div v-for="(menuGroup, groupIndex) in filteredMenuGroups" :key="groupIndex">
                    <h2 :class="[
                        'mb-4 text-xs uppercase flex leading-[20px] text-gray-400 dark:text-gray-500',
                        !isExpanded && !isHovered
                            ? 'lg:justify-center'
                            : 'justify-start',
                    ]">
                        <template v-if="shouldShowFullLogo">
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
                                <span :class="[
                                    'menu-item-icon',
                                    isSubmenuOpen(groupIndex, index)
                                        ? 'menu-item-icon-active'
                                        : 'menu-item-icon-inactive',
                                ]">
                                    <component :is="item.icon" />
                                </span>

                                <span v-if="shouldShowFullLogo" class="menu-item-text dark:text-gray-300">
                                    {{ item.name }}
                                </span>

                                <ChevronDownIcon v-if="shouldShowFullLogo" :class="[
                                    'ml-auto w-5 h-5 transition-transform duration-200',
                                    {
                                        'rotate-180 text-blue-500 dark:text-blue-400': isSubmenuOpen(
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
                                !isExpanded && !isHovered
                                    ? 'lg:justify-center'
                                    : 'lg:justify-start',
                            ]">
                                <span :class="[
                                    'menu-item-icon',
                                    isActive(item.path)
                                        ? 'menu-item-icon-active'
                                        : 'menu-item-icon-inactive',
                                ]">
                                    <component :is="item.icon" />
                                </span>

                                <span v-if="shouldShowFullLogo" class="menu-item-text dark:text-gray-300">
                                    {{ item.name }}
                                </span>
                            </Link>

                            <!-- Submenu -->
                            <div v-if="item.subItems && isSubmenuOpen(groupIndex, index) && shouldShowFullLogo"
                                class="mt-2">
                                <ul class="space-y-1 ml-9">
                                    <li v-for="subItem in item.subItems" :key="subItem.name">
                                        <Link v-if="subItem.path" :href="subItem.path" :class="[
                                            'menu-dropdown-item dark:text-gray-300',
                                            isActive(subItem.path)
                                                ? 'menu-dropdown-item-active'
                                                : 'menu-dropdown-item-inactive',
                                        ]">
                                            {{ subItem.name }}
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </aside>
</template>