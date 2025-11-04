<!--
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 -->
<template>
  <aside
    :class="sidebarClasses"
    :aria-label="ariaLabelSidebar"
    :aria-hidden="!isOpen && !isMobileOpen"
  >
    <div class="flex items-center gap-3 p-4 border-b">
      <div
        class="w-9 h-9 bg-primary rounded-lg flex items-center justify-center shadow-sm"
      >
        <span class="text-primary-foreground font-bold text-sm">PL</span>
      </div>
      <div class="flex flex-col">
        <span class="font-semibold text-foreground text-sm">Papa Leguas</span>
        <span class="text-xs text-muted-foreground">{{
          currentGuard.toUpperCase()
        }}</span>
      </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-3" aria-label="Menu principal">
      <ul class="space-y-0.5 px-2">
        <li>
          <SidebarItem
            :to="{ name: 'LandlordDashboard' }"
            icon="LayoutDashboard"
            label="Dashboard"
            :is-active="false"
          />
        </li>
        <li v-for="item in menuItems" :key="item.id">
          <SidebarGroup
            v-if="isGroupItem(item)"
            :icon="item.icon"
            :label="item.label"
            :is-open="isSubmenuOpen(item.id)"
            :is-active="isGroupActive(item)"
            @toggle="toggleSubmenu(item.id)"
          >
            <SidebarItem
              v-for="child in item.children"
              :key="child.id"
              :to="buildRouteLocation(child)"
              :icon="child.icon"
              :label="child.label"
              :is-active="isMenuActive(child)"
              :is-submenu="true"
            />
          </SidebarGroup>

          <SidebarItem
            v-else
            :to="buildRouteLocation(item)"
            :icon="item.icon"
            :label="item.label"
            :is-active="isMenuActive(item)"
          />
        </li>
      </ul>
    </nav>
  </aside>

  <div
    v-if="isMobileOpen"
    class="fixed inset-0 z-20 bg-black/50 lg:hidden transition-opacity duration-300"
    @click="closeMobileSidebar"
    aria-label="Fechar menu"
    role="button"
    tabindex="0"
    @keydown.enter="closeMobileSidebar"
    @keydown.space="closeMobileSidebar"
  ></div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useSidebar } from "../../composables/useSidebar";
import { useSidebarNavigation } from "../../composables/useSidebarNavigation";
import useAuth from "../../composables/useAuth";
import SidebarItem from "./SidebarItem.vue";
import SidebarGroup from "./SidebarGroup.vue";
import type { MenuItem } from "../../types/menu";

const { isOpen, isMobileOpen, toggleMobileSidebar } = useSidebar();
const { user } = useAuth();

const {
  menuItems,
  isMenuActive,
  isGroupActive,
  toggleSubmenu,
  isSubmenuOpen,
  buildRouteLocation,
} = useSidebarNavigation();

const currentGuard = computed(() => user.value?.current_guard || "web");

const sidebarClasses = computed(() => [
  "fixed left-0 top-0 z-30 h-full w-64 bg-background border-r border-border",
  "transition-transform duration-300 ease-in-out",
  isOpen.value ? "lg:translate-x-0" : "lg:-translate-x-full",
  isMobileOpen.value ? "translate-x-0" : "-translate-x-full",
]);

const ariaLabelSidebar = computed(() =>
  isMobileOpen.value || isOpen.value ? "Menu lateral aberto" : "Menu lateral fechado"
);

const isGroupItem = (item: MenuItem): boolean => {
  return item.type === "group" && !!item.children && item.children.length > 0;
};

const closeMobileSidebar = () => {
  if (isMobileOpen.value) {
    toggleMobileSidebar();
  }
};
</script>
