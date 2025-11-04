<template>
  <div class="min-h-screen bg-background">
    <ThemeProvider>
      <!-- Sidebar -->
      <AppSidebar v-if="authenticated" />

      <!-- Layout with Sidebar -->
      <div class="flex">
        <!-- Main Content -->
        <main
          :class="[
            'flex-1 transition-all duration-300 ease-in-out',
            isOpen && authenticated ? 'lg:ml-64' : 'lg:ml-0',
          ]"
        >
          <!-- Header -->
          <AppHeader v-if="authenticated" :guard-specific-items="guardSpecificItems" />
          <div class="p-6">
            <router-view />
          </div>
        </main>
      </div>

      <!-- Toast Notifications -->
      <Sonner />
    </ThemeProvider>
  </div>
</template>

<script setup lang="ts">
import AppHeader from "./../../components/layout/AppHeader.vue";
import AppSidebar from "./../../components/layout/AppSidebar.vue";
import ThemeProvider from "./../../components/layout/ThemeProvider.vue";
import Sonner from "./../../components/ui/sonner/Sonner.vue";
import { useSidebar } from "./../../composables/useSidebar";
import useAuth from "./../../composables/useAuth";
import { computed } from "vue";
import { BarChart3, UserCog, Users } from "lucide-vue-next";

const { authenticated } = useAuth();

const { isOpen } = useSidebar();

const guardSpecificItems = computed(() => {
  return [
    { href: "/admin/team", icon: Users, text: "Equipe" },
    { href: "/admin/settings", icon: UserCog, text: "Configurações do Tenant" },
    { href: "/admin/reports", icon: BarChart3, text: "Relatórios" },
  ];
});
</script>
