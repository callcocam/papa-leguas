<template>
  <!-- Main Content -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Welcome Section -->
    <div class="mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div>
          <h1 class="text-3xl font-bold text-foreground">Dashboard Landlord</h1>
          <p class="text-muted-foreground mt-2">
            Bem-vindo {{ user.name || "Administrador" }}! Gerencie todos os tenants da
            plataforma.
          </p>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <Card class="p-6 bg-card border shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
            <svg
              class="w-6 h-6 text-blue-600 dark:text-blue-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h10M7 15h10"
              />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-muted-foreground">Total Tenants</p>
            <p class="text-2xl font-bold text-foreground">{{ stats.totalTenants }}</p>
          </div>
        </div>
      </Card>

      <Card class="p-6 bg-card border shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <div class="p-2 bg-green-100 dark:bg-green-900/50 rounded-lg">
            <svg
              class="w-6 h-6 text-green-600 dark:text-green-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-muted-foreground">Tenants Ativos</p>
            <p class="text-2xl font-bold text-foreground">{{ stats.activeTenants }}</p>
          </div>
        </div>
      </Card>

      <Card class="p-6 bg-card border shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <div class="p-2 bg-orange-100 dark:bg-orange-900/50 rounded-lg">
            <svg
              class="w-6 h-6 text-orange-600 dark:text-orange-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
              />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-muted-foreground">Total Usuários</p>
            <p class="text-2xl font-bold text-foreground">{{ stats.totalUsers }}</p>
          </div>
        </div>
      </Card>

      <Card class="p-6 bg-card border shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center">
          <div class="p-2 bg-red-100 dark:bg-red-900/50 rounded-lg">
            <svg
              class="w-6 h-6 text-red-600 dark:text-red-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-muted-foreground">Problemas</p>
            <p class="text-2xl font-bold text-foreground">{{ stats.issues }}</p>
          </div>
        </div>
      </Card>
    </div>

    <!-- Action Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <Card class="p-6 bg-card border shadow-sm">
        <CardHeader class="p-0 pb-4">
          <CardTitle class="text-lg font-semibold text-foreground"
            >Ações Rápidas</CardTitle
          >
        </CardHeader>
        <CardContent class="p-0 space-y-3">
          <Button class="w-full justify-start" variant="outline">
            <svg
              class="w-4 h-4 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 6v6m0 0v6m0-6h6m-6 0H6"
              />
            </svg>
            Criar Novo Tenant
          </Button>
          <Button class="w-full justify-start" variant="outline">
            <svg
              class="w-4 h-4 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
              />
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
              />
            </svg>
            Configurações Globais
          </Button>
          <Button class="w-full justify-start" variant="outline">
            <svg
              class="w-4 h-4 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
              />
            </svg>
            Relatórios Gerais
          </Button>
        </CardContent>
      </Card>

      <Card class="p-6 bg-card border shadow-sm">
        <CardHeader class="p-0 pb-4">
          <CardTitle class="text-lg font-semibold text-foreground"
            >Tenants Recentes</CardTitle
          >
        </CardHeader>
        <CardContent class="p-0 space-y-3">
          <div
            v-for="tenant in recentTenants"
            :key="tenant.id"
            class="flex items-center justify-between p-3 bg-muted/50 dark:bg-muted/20 rounded-lg border"
          >
            <div>
              <p class="font-medium text-foreground">{{ tenant.name }}</p>
              <p class="text-sm text-muted-foreground">{{ tenant.email }}</p>
            </div>
            <span
              :class="getStatusColor(tenant.status)"
              class="px-2 py-1 text-xs rounded-full font-medium"
            >
              {{ tenant.status }}
            </span>
          </div>
        </CardContent>
      </Card>
    </div>
  </main>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import useAuth from "../../composables/useAuth";
import { Button } from "@/components/ui/button";

const { user, attempt } = useAuth();

// Stats data
const stats = ref({
  totalTenants: 0,
  activeTenants: 0,
  totalUsers: 0,
  issues: 0,
});

// Recent tenants data
const recentTenants = ref([
  {
    id: 1,
    name: "Empresa Alpha",
    slug: "empresa-alpha",
    email: "admin@alpha.com",
    status: "active",
    created_at: "2024-03-15T00:00:00.000Z",
  },
  {
    id: 2,
    name: "Empresa Beta",
    slug: "empresa-beta",
    email: "contato@beta.com",
    status: "active",
    created_at: "2024-03-10T00:00:00.000Z",
  },
  {
    id: 3,
    name: "Empresa Gamma",
    slug: "empresa-gamma",
    email: "info@gamma.com",
    status: "inactive",
    created_at: "2024-03-05T00:00:00.000Z",
  },
  {
    id: 4,
    name: "Empresa Delta",
    slug: "empresa-delta",
    email: "contato@delta.com",
    status: "canceled",
    created_at: "2024-01-20T00:00:00.000Z",
  },
]);

const getStatusColor = (status: string) => {
  switch (status) {
    case "active":
      return "bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-400";
    case "inactive":
      return "bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-400";
    case "canceled":
      return "bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-400";
    default:
      return "bg-muted text-muted-foreground";
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString("pt-BR");
};

onMounted(async () => {
  // Load user data
  await attempt();

  // Aqui você faria as chamadas para a API para carregar os dados reais
  // Simulando dados para demonstração
  stats.value = {
    totalTenants: 42,
    activeTenants: 38,
    totalUsers: 156,
    issues: 3,
  };
});
</script>
