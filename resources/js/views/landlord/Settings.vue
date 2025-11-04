<template>
  <div class="settings-view max-w-7xl mx-auto p-6 space-y-6">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-[400px]">
      <div class="text-center space-y-4">
        <div
          class="w-16 h-16 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"
        ></div>
        <p class="text-muted-foreground">Carregando informações do tenant...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-[400px]">
      <Card class="max-w-md border-destructive/50">
        <CardHeader>
          <CardTitle class="text-destructive flex items-center gap-2">
            <span class="text-2xl">⚠️</span>
            Erro ao carregar dados
          </CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-muted-foreground mb-4">{{ error }}</p>
          <Button @click="handleRetry" variant="outline" class="w-full">
            Tentar novamente
          </Button>
        </CardContent>
      </Card>
    </div>

    <!-- Content State -->
    <template v-else-if="tenantName">
      <!-- Header Card com Informações Principais do Tenant -->
      <Card
        class="bg-gradient-to-r from-primary/5 to-primary/10 border-0 shadow-lg"
      >
        <CardHeader>
          <div class="flex flex-col md:flex-row items-center gap-6">
            <!-- Icon/Avatar -->
            <div
              class="w-24 h-24 bg-gradient-to-br from-primary to-primary/70 rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-lg"
            >
              {{ tenantInitials }}
            </div>

            <!-- Informações Principais -->
            <div class="flex-1 text-center md:text-left space-y-2">
              <CardTitle class="text-3xl">{{ tenantName }}</CardTitle>
              <div v-if="tenantEmail" class="text-lg text-muted-foreground">
                <InfoReander :column="tenantEmail" />
              </div>
              <div v-if="tenantDomain" class="text-sm text-muted-foreground">
                <span class="font-medium">Domínio:</span> {{ tenantDomain }}
              </div>
            </div>
          </div>
        </CardHeader>
      </Card>

      <!-- Grid de Cards dinâmicos -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div
          v-for="item in cardItems"
          :key="item.id"
          :class="[item.gridCols === '3' ? 'lg:col-span-2' : '']"
        >
          <InfoReander :column="item" />
        </div>
      </div>
    </template>

    <!-- Empty State -->
    <div v-else class="flex items-center justify-center min-h-[400px]">
      <Card class="max-w-md">
        <CardHeader>
          <CardTitle class="text-center">Nenhum tenant encontrado</CardTitle>
        </CardHeader>
        <CardContent>
          <p class="text-center text-muted-foreground">
            Não foi possível identificar as informações do tenant atual.
          </p>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed, onMounted } from "vue";
import useTenantSettings from "../../composables/useTenantSettings";
import InfoReander from "../../components/infolist/InfoReander.vue";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

const { tenantSettings, loading, error, fetchTenantSettings, clearError } =
  useTenantSettings();

onMounted(() => {
  fetchTenantSettings();
});

const items = computed(() => {
  const data = tenantSettings.value?.data;
  if (data && typeof data === "object" && !Array.isArray(data)) {
    return Object.entries(data).map(([key, value]: [string, any]) => ({
      id: key,
      label: value.label || key,
      text: value.text || value,
      icon: value.icon || "Settings",
      tooltip: value.tooltip || "",
      type: value.type || "text",
      component: value.component,
      columns: value.columns || {},
      title: value.title,
      description: value.description,
      gridCols: value.gridCols,
      collapsible: value.collapsible,
      defaultExpanded: value.defaultExpanded,
      color: value.color,
      value: value.value,
    }));
  }
  return Array.isArray(data) ? data : [];
});

const tenantName = computed(() => {
  const nameItem = items.value.find((item: any) => item.id === "name");
  return nameItem?.text || tenantSettings.value?.name || "";
});

const tenantEmail = computed(() => {
  return items.value.find((item: any) => item.id === "email");
});

const tenantDomain = computed(() => {
  return tenantSettings.value?.domain || "";
});

const tenantInitials = computed(() => {
  return tenantName.value
    .split(" ")
    .map((name: string) => name.charAt(0))
    .slice(0, 2)
    .join("")
    .toUpperCase();
});

const cardItems = computed(() => {
  return items.value.filter((item: any) => item.type === "card");
});

const handleRetry = () => {
  clearError();
  fetchTenantSettings();
};
</script>

<style scoped>
.settings-view {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>