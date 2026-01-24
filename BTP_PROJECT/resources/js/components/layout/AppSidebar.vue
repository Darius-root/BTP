<script setup>
import { computed, ref } from "vue";
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

/* PAGE */
const page = usePage();

/* PERMISSIONS */
const can = (permission) =>
    !permission || page.props.auth?.permissions?.includes(permission);

/* SIDEBAR (SOURCE DE VÉRITÉ UNIQUE) */
const {
    isExpanded,
    isMobileOpen,
    showSidebar,
    openSubmenu,
    setIsHovered,
    toggleSubmenu,
} = useSidebar();

/* MENU CONFIG */
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
                    { name: "Utilisateurs", path: "/organisations/users", permission: "ORG_ORGANISATION_USER_VIEW" },
                    { name: "Rôles", path: "/organisations/roles", permission: "ORG_ORGANISATION_ROLE_VIEW" },
                ],
            },
            {
                name: "Templates", icon: ListIcon,
                subItems: [
                    { name: "Devis Estimatif", path: "/templates-estimatif",permission: "ORG_TEMPLATE_DEVIS_ESTIMATIF_VIEW" },
                    { name: "Devis Quantitatif", path: "/templates-estimatif-qte", permission: "ORG_TEMPLATE_DEVIS_QUANTITATIF_VIEW"        },

                ]
            },
            { name: "Clients", icon: UserCircleIcon, path: "/clients", permission: "ORG_CLIENT_VIEW" },
            { name: "Projets", icon: BoxCubeIcon, path: "/projets", permission: "ORG_PROJET_VIEW" },
            {name: "Bordereaux de prix", icon: ListIcon, path: "/bordereaux", permission: "SYSTEM_CORPS_ETAT_VIEW"  },
            { name: "Collections de prix", icon: ListIcon, path: "/collections-prix" , permission: "SYSTEM_COLLECTION_VIEW" },

        ],
    },
    {
        title: "Fonctions",
        items: [
            {
                name: "Configurations",
                icon: ListIcon,
                subItems: [
                    { name: "Corps d'état", path: "/corps-etat", permission: "SYSTEM_CORPS_ETAT_VIEW" },
                    { name: "Devises", path: "/devises", permission: "SYSTEM_DEVISE_VIEW" },
                    { name: "Matériaux", path: "/materiaux", permission: "SYSTEM_MATERIAU_VIEW" },
                    { name: "Unités de mesure", path: "/unites-mesure", permission: "SYSTEM_UNITE_MESURE_VIEW" },
                    { name: "Communes", path: "/communes", permission: "SYSTEM_COMMUNE_VIEW" },
                    { name: "Arrondissements", path: "/arrondissements", permission: "SYSTEM_ARRONDISSEMENT_VIEW" },
                    { name: "Niveaux Bâtiment", path: "/niveaux-batiment", permission: "SYSTEM_NIVEAU_BATIMENT_VIEW" },

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
                    { name: "Collecteurs", path: "/collectors", permission: "SYSTEM_COLLECTOR_VIEW" },
                    { name: "Permissions", path: "/permissions", permission: "SYSTEM_PERMISSION_VIEW" },
                    { name: "Rôles", path: "/roles", permission: "SYSTEM_ROLE_VIEW" },
                ],
            },
        ],
    },
];

/* FILTER */
const filteredMenuGroups = computed(() =>
    menuGroups
        .map(group => {
            const items = group.items
                .map(item => {
                    if (item.subItems) {
                        const subItems = item.subItems.filter(s => can(s.permission));
                        return subItems.length ? { ...item, subItems } : null;
                    }
                    return item.permission && !can(item.permission) ? null : item;
                })
                .filter(Boolean);
            return items.length ? { ...group, items } : null;
        })
        .filter(Boolean)
);

/* HELPERS */
const submenuKey = (g, i) => `${g}-${i}`;
const isSubmenuOpen = (g, i) => openSubmenu.value === submenuKey(g, i);

/* HOVER ITEM (SIDEBAR RÉDUITE SEULEMENT) */
const onItemHover = (g, i) => {
    if (isExpanded.value || isMobileOpen.value) return;
    openSubmenu.value = submenuKey(g, i);
};

const onItemLeave = (g, i) => {
    if (isExpanded.value || isMobileOpen.value) return;
    if (openSubmenu.value === submenuKey(g, i)) {
        openSubmenu.value = null;
    }
};
</script>

<template>
    <aside :class="[
        'fixed mt-16 flex flex-col lg:mt-0 top-0 px-5 left-0 h-screen transition-all duration-300 ease-in-out z-50 border-r',
        showSidebar ? 'lg:w-72.5' : 'lg:w-22.5',
        isMobileOpen ? 'translate-x-0 w-72.5' : '-translate-x-full',
        'lg:translate-x-0',
        'bg-white dark:bg-gray-900 dark:border-gray-800 border-gray-200 text-gray-900 dark:text-gray-300',
    ]" @mouseenter="setIsHovered(true)" @mouseleave="setIsHovered(false)">
        <!-- LOGO -->
        <div class="py-6 shrink-0">
            <div v-if="showSidebar" class="flex justify-center">
                <Link href="/" class="flex items-center space-x-3">
                    <div
                        class="w-12 h-12 rounded-xl bg-linear-to-br from-blue-600 to-indigo-600 flex items-center justify-center shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" />
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <div class="text-xl font-extrabold dark:text-white">
                            BTP<span class="text-blue-600 dark:text-blue-400"> Bénin</span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Gestion des Prix
                        </div>
                    </div>
                </Link>
            </div>

            <div v-else class="flex justify-center">
                <Link href="/" class="flex items-center justify-center">
                    <div
                        class="w-12 h-12 rounded-xl bg-linear-to-br from-blue-600 to-indigo-600 flex items-center justify-center shadow-lg">
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
        <nav class="flex-1 overflow-y-auto no-scrollbar">
            <div class="flex flex-col gap-4">
                <div v-for="(menuGroup, groupIndex) in filteredMenuGroups" :key="groupIndex">
                    <!-- TITRE SECTION -->
                    <h2 class="mb-4 text-xs uppercase flex text-gray-400 dark:text-gray-500"
                        :class="showSidebar ? 'justify-start' : 'justify-center'">
                        <template v-if="showSidebar">{{ menuGroup.title }}</template>
                        <HorizontalDots v-else class="w-5 h-5" />
                    </h2>

                    <ul class="flex flex-col gap-4">
                        <li v-for="(item, index) in menuGroup.items" :key="item.name">
                            <!-- ITEM AVEC SOUS-MENU -->
                            <div v-if="item.subItems" class="relative" @mouseenter="onItemHover(groupIndex, index)"
                                @mouseleave="onItemLeave(groupIndex, index)">
                                <button @click="toggleSubmenu(submenuKey(groupIndex, index))"
                                    class="menu-item group w-full flex items-center py-3 px-4 rounded-lg transition-colors duration-200"
                                    :class="{
                                        'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400': isSubmenuOpen(groupIndex, index),
                                        'hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300': !isSubmenuOpen(groupIndex, index),
                                        'justify-start': showSidebar,
                                        'justify-center': !showSidebar,
                                    }">
                                    <component :is="item.icon" class="w-5 h-5 shrink-0" />
                                    <span v-if="showSidebar" class="ml-3 font-medium">{{ item.name }}</span>
                                    <ChevronDownIcon v-if="showSidebar" class="ml-auto w-5 h-5 transition-transform"
                                        :class="{ 'rotate-180': isSubmenuOpen(groupIndex, index) }" />
                                </button>

                                <!-- TOOLTIP SUBMENU -->
                                <div v-if="isSubmenuOpen(groupIndex, index) && !showSidebar"
                                    class="fixed left-24.5 z-9999">
                                    <div
                                        class="bg-white dark:bg-gray-800 shadow-xl rounded-lg py-2 min-w-50 border border-gray-200 dark:border-gray-700">
                                        <ul>
                                            <li v-for="subItem in item.subItems" :key="subItem.name">
                                                <Link :href="subItem.path"
                                                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                                                    {{ subItem.name }}
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- SUBMENU NORMAL -->
                                <div v-if="isSubmenuOpen(groupIndex, index) && showSidebar" class="mt-2 ml-9">
                                    <ul class="space-y-2">
                                        <li v-for="subItem in item.subItems" :key="subItem.name">
                                            <Link :href="subItem.path"
                                                class="block py-2 px-4 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                                {{ subItem.name }}
                                            </Link>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- ITEM SIMPLE -->
                            <Link v-else :href="item.path"
                                class="menu-item group flex items-center py-3 px-4 rounded-lg transition-colors duration-200 relative"
                                :class="{
                                    'justify-start': showSidebar,
                                    'justify-center': !showSidebar,
                                    'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400': page.url === item.path,
                                    'hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300': page.url !== item.path,
                                }">
                                <component :is="item.icon" class="w-5 h-5 shrink-0" />
                                <span v-if="showSidebar" class="ml-3 font-medium">{{ item.name }}</span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </aside>
</template>
