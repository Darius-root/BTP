import {
  ref,
  computed,
  onMounted,
  onUnmounted,
  provide,
  inject,
} from 'vue'
import type { Ref } from 'vue'

interface SidebarContextType {
  /* state */
  isExpanded: Ref<boolean>
  isMobileOpen: Ref<boolean>
  isHovered: Ref<boolean>
  showSidebar: Ref<boolean>

  activeItem: Ref<string | null>
  openSubmenu: Ref<string | null>

  /* actions */
  toggleSidebar: () => void
  toggleMobileSidebar: () => void
  setIsHovered: (value: boolean) => void
  setActiveItem: (item: string | null) => void
  toggleSubmenu: (item: string) => void
}

const SidebarSymbol = Symbol('Sidebar')

/* =========================
   PROVIDER
========================= */
export function useSidebarProvider() {
  const isExpanded = ref(true)          // état desktop manuel
  const isMobileOpen = ref(false)       // état mobile
  const isHovered = ref(false)          // hover desktop
  const isMobile = ref(false)

  const activeItem = ref<string | null>(null)
  const openSubmenu = ref<string | null>(null)

  /* ---------- Responsive ---------- */
  const handleResize = () => {
    isMobile.value = window.innerWidth < 768

    if (!isMobile.value) {
      isMobileOpen.value = false
    } else {
      isHovered.value = false
    }
  }

  onMounted(() => {
    handleResize()
    window.addEventListener('resize', handleResize)
  })

  onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
  })

  /* ---------- Computed clé ---------- */
  const showSidebar = computed(() => {
    if (isMobile.value) {
      return isMobileOpen.value
    }
    return isExpanded.value || isHovered.value
  })

  /* ---------- Actions ---------- */
  const toggleSidebar = () => {
    if (isMobile.value) {
      isMobileOpen.value = !isMobileOpen.value
    } else {
      isExpanded.value = !isExpanded.value
    }
  }

  const toggleMobileSidebar = () => {
    isMobileOpen.value = !isMobileOpen.value
  }

  const setIsHovered = (value: boolean) => {
    if (!isMobile.value && !isExpanded.value) {
      isHovered.value = value
    }
  }

  const setActiveItem = (item: string | null) => {
    activeItem.value = item
  }

  const toggleSubmenu = (item: string) => {
    openSubmenu.value = openSubmenu.value === item ? null : item
  }

  /* ---------- Context ---------- */
  const context: SidebarContextType = {
    isExpanded,
    isMobileOpen,
    isHovered,
    showSidebar,

    activeItem,
    openSubmenu,

    toggleSidebar,
    toggleMobileSidebar,
    setIsHovered,
    setActiveItem,
    toggleSubmenu,
  }

  provide(SidebarSymbol, context)

  return context
}

/* =========================
   CONSUMER
========================= */
export function useSidebar(): SidebarContextType {
  const context = inject<SidebarContextType>(SidebarSymbol)

  if (!context) {
    throw new Error(
      'useSidebar must be used within useSidebarProvider',
    )
  }

  return context
}
