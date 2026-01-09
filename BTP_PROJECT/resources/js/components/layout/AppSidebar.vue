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

const page = usePage();

/**
 * Permission helper (team-aware via Inertia share)
 */
const can = (permission) => {
    return page.props.auth?.permissions?.includes(permission);
};

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
            {
                name: "Dashboard",
                icon: GridIcon,
                path: "/dashboard",
            },
            {
                name: "Profil",
                icon: UserCircleIcon,
                path: "/profile",
            },
            {
                name: "Organisations",
                icon: ListIcon,
                permission: "ORG_ORGANISATION_VIEW",
                subItems: [
                    {
                        name: "Mes organisations",
                        path: "/organisations",
                        permission: "ORG_ORGANISATION_VIEW",
                    },
                    {
                        name: "Utilisateurs",
                        path: "/organisations/users",
                        permission: "ORG_ORGANISATIONUSER_VIEW",
                    },
                    {
                        name: "Rôles",
                        path: "/organisations/roles",
                        permission: "ORG_ORGANISATION_ROLE_VIEW",
                    },
                ],
            },
            {
                name: "Clients",
                icon: UserCircleIcon,
                path: "/clients",
                permission: "ORG_CLIENT_VIEW",
            },
            {
                name: "Projets",
                icon: BoxCubeIcon,
                path: "/projets",
                permission: "ORG_PROJET_VIEW",
            },
            {
                name: "Collections de prix",
                icon: ListIcon,
                path: "/collections-prix",
                permission: "",
            },
        ],
    },

    {
        title: "Fonctions",
        items: [
            {
                name: "Configurations",
                icon: ListIcon,
                permission: "manage settings",
                subItems: [
                    {
                        name: "Rôles",
                        path: "/roles",
                        permission: "SYSTEM_ROLE_VIEW",
                    },
                    {
                        name: "Permissions",
                        path: "/permissions",
                        permission: "SYSTEM_PERMISSION_VIEW",
                    },
                    {
                        name: "Communes",
                        path: "/communes",
                        permission: "SYSTEM_COMMUNE_VIEW",
                    },
                    {
                        name: "Arrondissements",
                        path: "/arrondissements",
                        permission: "SYSTEM_ARRONDISSEMENT_VIEW",
                    },
                    {
                        name: "Unités de mesure",
                        path: "/unites-mesure",
                        permission: "SYSTEM_UNITE_MESURE_VIEW",
                    },
                    {
                        name: "Matériaux",
                        path: "/materiaux",
                        permission: "SYSTEM_MATERIAU_VIEW",
                    },
                    {
                        name: "Devises",
                        path: "/devises",
                        permission: "SYSTEM_DEVISE_VIEW",
                    },
                    {
                        name: "Corps d’état",
                        path: "/corps-etat",
                        permission: "SYSTEM_CORPS_ETAT_VIEW",
                    },
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
                permission: "view users",
                subItems: [
                    { name: "Liste", path: "/users", permission: "view users" },
                    {
                        name: "Rôles",
                        path: "/permissions",
                        permission: "manage users",
                    },
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
                    // Submenus
                    if (item.subItems) {
                        const subItems = item.subItems.filter(
                            (sub) => !sub.permission || can(sub.permission)
                        );

                        if (!subItems.length) return null;

                        return { ...item, subItems };
                    }

                    // Single item
                    if (item.permission && !can(item.permission)) {
                        return null;
                    }

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
</script>

<template>
    <aside
        :class="[
            'fixed mt-16 flex flex-col lg:mt-0 top-0 px-5 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-50 border-r border-gray-200',
            {
                'lg:w-[290px]': isExpanded || isHovered || isMobileOpen,
                'lg:w-[90px]': !isExpanded && !isHovered,
                'translate-x-0 w-[290px]': isMobileOpen,
                '-translate-x-full': !isMobileOpen,
                'lg:translate-x-0': true,
            },
        ]"
        @mouseenter="!isExpanded && (isHovered = true)"
        @mouseleave="isHovered = false"
    >
        <!-- LOGO -->
        <div
            :class="[
                'py-8 flex',
                !isExpanded && !isHovered
                    ? 'lg:justify-center'
                    : 'justify-start',
            ]"
        >
            <Link href="/">
                <img
                    v-if="isExpanded || isHovered || isMobileOpen"
                    src="/images/logo/logo.svg"
                    alt="Logo"
                    width="150"
                />
                <img
                    v-else
                    src="/images/logo/logo-icon.svg"
                    alt="Logo"
                    width="32"
                />
            </Link>
        </div>

        <!-- MENU -->
        <nav class="flex-1 overflow-y-auto no-scrollbar">
            <div
                v-for="(menuGroup, groupIndex) in filteredMenuGroups"
                :key="groupIndex"
                class="mb-6"
            >
                <h2
                    :class="[
                        'mb-4 text-xs uppercase text-gray-400 flex',
                        !isExpanded && !isHovered
                            ? 'lg:justify-center'
                            : 'justify-start',
                    ]"
                >
                    <span v-if="isExpanded || isHovered || isMobileOpen">
                        {{ menuGroup.title }}
                    </span>
                    <HorizontalDots v-else />
                </h2>

                <ul class="flex flex-col gap-2">
                    <li
                        v-for="(item, index) in menuGroup.items"
                        :key="item.name"
                    >
                        <!-- SUBMENU -->
                        <button
                            v-if="item.subItems"
                            @click="toggleSubmenu(groupIndex, index)"
                            class="menu-item group w-full"
                        >
                            <component :is="item.icon" />
                            <span
                                v-if="isExpanded || isHovered || isMobileOpen"
                                class="menu-item-text"
                            >
                                {{ item.name }}
                            </span>
                            <ChevronDownIcon
                                v-if="isExpanded || isHovered || isMobileOpen"
                                class="ml-auto w-4 h-4"
                            />
                        </button>

                        <!-- SIMPLE LINK -->
                        <Link v-else :href="item.path" class="menu-item">
                            <component :is="item.icon" />
                            <span
                                v-if="isExpanded || isHovered || isMobileOpen"
                                class="menu-item-text"
                            >
                                {{ item.name }}
                            </span>
                        </Link>

                        <!-- DROPDOWN -->
                        <ul
                            v-if="
                                item.subItems &&
                                isSubmenuOpen(groupIndex, index)
                            "
                            class="ml-9 mt-2 space-y-1"
                        >
                            <li
                                v-for="subItem in item.subItems"
                                :key="subItem.name"
                            >
                                <Link
                                    :href="subItem.path"
                                    class="menu-dropdown-item"
                                >
                                    {{ subItem.name }}
                                </Link>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </aside>
</template>
