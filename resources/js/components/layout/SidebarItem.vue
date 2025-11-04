<template>
  <router-link
    :to="to"
    :class="computedClasses"
    :aria-current="isActive ? 'page' : undefined"
    :aria-label="ariaLabel"
  >
    <Icon :is="icon" :class="iconSize" />
    <span class="truncate">{{ label }}</span>
  </router-link>
</template>

<script setup lang="ts">
import { computed } from "vue";
import type { RouteLocationNamedRaw } from "vue-router";
import Icon from "../icon.vue";
import { ACTIVE_MENU_CLASSES, ACTIVE_SUBMENU_CLASSES } from "../../types/menu";

interface SidebarItemProps {
  to: RouteLocationNamedRaw;
  icon: string;
  label: string;
  isActive: boolean;
  isSubmenu?: boolean;
}

const props = withDefaults(defineProps<SidebarItemProps>(), {
  isSubmenu: false,
});

const activeClasses = computed(() =>
  props.isSubmenu ? ACTIVE_SUBMENU_CLASSES : ACTIVE_MENU_CLASSES
);

const computedClasses = computed(() => [
  activeClasses.value.base,
  activeClasses.value.hover,
  props.isActive ? activeClasses.value.active : activeClasses.value.inactive,
]);

const iconSize = computed(() =>
  props.isSubmenu 
    ? "h-4 w-4 flex-shrink-0 opacity-70" 
    : "h-5 w-5 flex-shrink-0"
);

const ariaLabel = computed(() =>
  props.isActive ? `${props.label} (página atual)` : props.label
);
</script>
