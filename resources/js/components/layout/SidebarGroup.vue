<template>
  <Collapsible :open="isOpen" @update:open="handleToggle">
    <CollapsibleTrigger as-child>
      <button
        :class="computedTriggerClasses"
        :aria-expanded="isOpen"
        :aria-label="ariaLabel"
      >
        <Icon :is="icon" class="h-5 w-5 flex-shrink-0" />
        <span class="flex-1 text-left truncate">{{ label }}</span>
        <ChevronDown :class="chevronClasses" aria-hidden="true" />
      </button>
    </CollapsibleTrigger>
    <CollapsibleContent class="space-y-0.5 mt-0.5 ml-2">
      <slot />
    </CollapsibleContent>
  </Collapsible>
</template>

<script setup lang="ts">
import { computed } from "vue";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "@/components/ui/collapsible";
import { ChevronDown } from "lucide-vue-next";
import Icon from "../icon.vue";
import { ACTIVE_GROUP_CLASSES } from "../../types/menu";

interface SidebarGroupProps {
  icon: string;
  label: string;
  isOpen: boolean;
  isActive: boolean;
}

interface SidebarGroupEmits {
  (event: "toggle"): void;
}

const props = defineProps<SidebarGroupProps>();
const emit = defineEmits<SidebarGroupEmits>();

const computedTriggerClasses = computed(() => [
  ACTIVE_GROUP_CLASSES.base,
  ACTIVE_GROUP_CLASSES.hover,
  props.isActive ? ACTIVE_GROUP_CLASSES.active : ACTIVE_GROUP_CLASSES.inactive,
]);

const chevronClasses = computed(() => [
  "h-4 w-4 transition-transform duration-200 opacity-50",
  props.isOpen ? "rotate-180" : "",
]);

const ariaLabel = computed(() =>
  props.isOpen ? `${props.label} (expandido)` : `${props.label} (recolhido)`
);

const handleToggle = () => {
  emit("toggle");
};
</script>
