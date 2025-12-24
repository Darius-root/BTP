<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

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

const page = usePage()

const { isExpanded, isMobileOpen, isHovered, openSubmenu } = useSidebar()

const menuGroups = [
    {
        title: 'Menu',
        items: [
            { icon: GridIcon, name: 'Dashboard', path: '/dashboard' },
            { icon: CalenderIcon, name: 'Calendrier', path: '/calendar' },
            { icon: UserCircleIcon, name: 'Profil', path: '/profile' },
            {
                name: 'Bordereaux',
                icon: ListIcon,
                subItems: [
                    { name: 'Liste', path: '/bordereaux' },
                    { name: 'Importer', path: '/bordereaux/import' },
                ],
            },
            {
                name: 'Formulaires',
                icon: ListIcon,
                subItems: [
                    { name: 'Éléments', path: '/forms/elements' },
                ],
            },
        ],
    },
    {
        title: 'Autres',
        items: [
            {
                icon: PieChartIcon,
                name: 'Graphiques',
                subItems: [
                    { name: 'Ligne', path: '/charts/line' },
                    { name: 'Barres', path: '/charts/bar' },
                ],
            },
            {
                icon: PlugInIcon,
                name: 'Authentification',
                subItems: [
                    { name: 'Connexion', path: '/login' },
                    { name: 'Inscription', path: '/register' },
                ],
            },
        ],
    },
]

const isActive = (path) => page.url === path

const toggleSubmenu = (groupIndex, itemIndex) => {
    const key = `${groupIndex}-${itemIndex}`
    openSubmenu.value = openSubmenu.value === key ? null : key
}

const isSubmenuOpen = (groupIndex, itemIndex) =>
    openSubmenu.value === `${groupIndex}-${itemIndex}`
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
        <!-- Logo -->
        <div :class="['py-8 flex', !isExpanded && !isHovered ? 'lg:justify-center' : 'justify-start']">
            <Link href="/">
                <img v-if="isExpanded || isHovered || isMobileOpen" class="dark:hidden" src="/images/logo/logo.svg"
                    alt="Logo" width="150" height="40" />
                <img v-if="isExpanded || isHovered || isMobileOpen" class="hidden dark:block"
                    src="/images/logo/logo-dark.svg" alt="Logo" width="150" height="40" />
                <img v-else src="/images/logo/logo-icon.svg" alt="Logo" width="32" height="32" />
            </Link>
        </div>

    </div>
  </aside>
</template>
