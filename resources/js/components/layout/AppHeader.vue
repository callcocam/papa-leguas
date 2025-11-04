<template>
  <header class="flex w-full bg-background border-b border-border z-10">
    <div class="flex flex-col items-center justify-between grow lg:flex-row lg:px-6">
      <div
        class="flex items-center justify-between w-full gap-2 px-4 py-3 border-b border-border lg:justify-normal lg:border-b-0 lg:px-0 lg:py-2">
        <button @click="handleToggle"
          class="flex items-center justify-center w-9 h-9 text-foreground hover:text-accent-foreground border border-border rounded-lg hover:bg-accent transition-colors lg:h-10 lg:w-10"
          :class="[
            isMobileOpen
              ? 'lg:bg-transparent bg-accent'
              : '',
          ]">
          <!-- Ícone hamburger (padrão) -->
          <svg v-if="!isMobileOpen" width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
              d="M1 2C1 1.44772 1.44772 1 2 1H18C18.5523 1 19 1.44772 19 2C19 2.55228 18.5523 3 18 3H2C1.44772 3 1 2.55228 1 2ZM1 8C1 7.44772 1.44772 7 2 7H18C18.5523 7 19 7.44772 19 8C19 8.55228 18.5523 9 18 9H2C1.44772 9 1 8.55228 1 8ZM2 13C1.44772 13 1 13.4477 1 14C1 14.5523 1.44772 15 2 15H12C12.5523 15 13 14.5523 13 14C13 13.4477 12.5523 13 12 13H2Z"
              fill="currentColor" />
          </svg>

          <!-- Ícone X (fechar) - quando mobile sidebar está aberta -->
          <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
              d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
              fill="currentColor" />
          </svg>
        </button>
        <HeaderLogo />
        <button @click="toggleApplicationMenu"
          class="flex items-center justify-center w-9 h-9 text-foreground hover:text-accent-foreground rounded-lg hover:bg-accent transition-colors lg:hidden">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
              d="M5.99902 10.4951C6.82745 10.4951 7.49902 11.1667 7.49902 11.9951V12.0051C7.49902 12.8335 6.82745 13.5051 5.99902 13.5051C5.1706 13.5051 4.49902 12.8335 4.49902 12.0051V11.9951C4.49902 11.1667 5.1706 10.4951 5.99902 10.4951ZM17.999 10.4951C18.8275 10.4951 19.499 11.1667 19.499 11.9951V12.0051C19.499 12.8335 18.8275 13.5051 17.999 13.5051C17.1706 13.5051 16.499 12.8335 16.499 12.0051V11.9951C16.499 11.1667 17.1706 10.4951 17.999 10.4951ZM13.499 11.9951C13.499 11.1667 12.8275 10.4951 11.999 10.4951C11.1706 10.4951 10.499 11.1667 10.499 11.9951V12.0051C10.499 12.8335 11.1706 13.5051 11.999 13.5051C12.8275 13.5051 13.499 12.8335 13.499 12.0051V11.9951Z"
              fill="currentColor" />
          </svg>
        </button>
        <SearchBar />
      </div>

      <div :class="[isApplicationMenuOpen ? 'flex' : 'hidden']"
        class="items-center justify-between w-full gap-4 px-4 py-3 bg-background border-t border-border lg:flex lg:justify-end lg:px-0 lg:border-t-0 lg:bg-transparent">
        <div class="flex items-center gap-2">
          <ThemeToggle />
          <NotificationMenu />
        </div>
        <UserMenu :user="getUserData" :guard-specific-items="guardSpecificItems" />
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import useAuth from '../../composables/useAuth'
const { user } = useAuth()
import { useSidebar } from '../../composables/useSidebar'
import SearchBar from './header/SearchBar.vue'
import HeaderLogo from './header/HeaderLogo.vue'
import NotificationMenu from './header/NotificationMenu.vue'
import UserMenu from './header/UserMenu.vue'
import ThemeToggle from '../common/ThemeToggle.vue'

defineProps<{
  guardSpecificItems?: Array<any>
}>()

const { isMobileOpen, toggleSidebar, toggleMobileSidebar } = useSidebar()

const handleToggle = () => {
  // Desktop: apenas toggle da sidebar principal
  // Mobile: apenas toggle do overlay
  if (window.innerWidth >= 1024) {
    toggleSidebar()
  } else {
    toggleMobileSidebar()
  }
}

const getUserData = computed(() => {
  return user.value
})


const isApplicationMenuOpen = ref(false)

const toggleApplicationMenu = () => {
  isApplicationMenuOpen.value = !isApplicationMenuOpen.value
}
</script>
