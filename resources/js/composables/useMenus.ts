import { ref, computed, readonly } from 'vue'
import useAuth from './useAuth'
import axios from 'axios'

interface MenuRoute {
  name: string
  uri: string
  method: string
  label: string
  icon: string
  action: string
}

interface MenuItem {
  id: string
  label: string
  icon: string
  order: number
  group?: string
  badge?: string
  routes: MenuRoute[]
  controller: string
  resource: string
  children?: MenuItem[]
  type?: string
}

interface NavigationItem {
  id: string
  label: string
  icon: string
  href: string | null
  badge?: string
  children?: NavigationItem[]
}

interface MenuResponse {
  success: boolean
  data: {
    menus: MenuItem[]
    navigation: NavigationItem[]
    guard: string
  }
  message: string
}

const menus = ref<MenuItem[]>([])
const navigation = ref<NavigationItem[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const currentGuard = ref<string>('')

export function useMenus() {
  const { user } = useAuth()

  const isLoaded = computed(() => menus.value.length > 0)
  
  const groupedMenus = computed(() => {
    const groups: Record<string, MenuItem[]> = {}
    const ungrouped: MenuItem[] = []
    
    menus.value.forEach(menu => {
      if (menu.type === 'group') {
        groups[menu.id] = menu.children || []
      } else if (!menu.group) {
        ungrouped.push(menu)
      }
    })
    
    return { groups, ungrouped }
  })

  const fetchMenus = async (): Promise<void> => {
    if (!user.value) {
      error.value = 'User not authenticated'
      return
    }

    try {
      loading.value = true
      error.value = null

      const response = await axios.get<MenuResponse>('/api/landlord/menus')
      
      if (response.data.success) {
        menus.value = response.data.data.menus
        navigation.value = response.data.data.navigation
        currentGuard.value = response.data.data.guard
      } else {
        throw new Error(response.data.message || 'Failed to fetch menus')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Failed to fetch menus'
      console.error('Error fetching menus:', err)
    } finally {
      loading.value = false
    }
  }

  const findMenuById = (id: string): MenuItem | null => {
    for (const menu of menus.value) {
      if (menu.id === id) return menu
      
      if (menu.children) {
        for (const child of menu.children) {
          if (child.id === id) return child
        }
      }
    }
    return null
  }

  const findNavigationById = (id: string): NavigationItem | null => {
    for (const nav of navigation.value) {
      if (nav.id === id) return nav
      
      if (nav.children) {
        for (const child of nav.children) {
          if (child.id === id) return child
        }
      }
    }
    return null
  }

  const getMenuRoutes = (menuId: string): MenuRoute[] => {
    const menu = findMenuById(menuId)
    return menu?.routes || []
  }

  const getPrimaryRoute = (menuId: string): string | null => {
    const nav = findNavigationById(menuId)
    return nav?.href || null
  }

  const clearMenus = (): void => {
    menus.value = []
    navigation.value = []
    currentGuard.value = ''
    error.value = null
  }

  const refreshMenus = async (): Promise<void> => {
    clearMenus()
    await fetchMenus()
  }

  return {
    // State
    menus: readonly(menus),
    navigation: readonly(navigation),
    loading: readonly(loading),
    error: readonly(error),
    currentGuard: readonly(currentGuard),
    
    // Computed
    isLoaded,
    groupedMenus,
    
    // Actions
    fetchMenus,
    findMenuById,
    findNavigationById,
    getMenuRoutes,
    getPrimaryRoute,
    clearMenus,
    refreshMenus,
  }
}