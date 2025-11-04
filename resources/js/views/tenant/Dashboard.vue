<template>
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-foreground">
                        {{ user.tenant?.name || 'Dashboard Tenant' }}
                    </h1>
                    <p class="text-muted-foreground mt-2">
                        Bem-vindo {{ user.name || 'Usuário' }}! Gerencie seus dados e configurações.
                    </p>
                </div> 
            </div>
        </div>

        <!-- Tenant Info Card -->
        <Card v-if="user.tenant"
            class="p-6 mb-8 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/50 dark:to-indigo-950/50 border-blue-200 dark:border-blue-800">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-foreground">{{ user.tenant.name }}</h2>
                    <p class="text-sm text-muted-foreground mt-1">{{ user.tenant.email || user.tenant.domain }}</p>
                    <p class="text-sm text-muted-foreground">{{ user.tenant.document || 'Não informado' }}</p>
                </div>
                <div class="text-right">
                    <span :class="getStatusColor(user.tenant.status)"
                        class="px-3 py-1 text-sm rounded-full font-medium">
                        {{ getStatusLabel(user.tenant.status) }}
                    </span>
                    <p class="text-xs text-muted-foreground mt-2">
                        Desde {{ formatDate(user.tenant.created_at) }}
                    </p>
                </div>
            </div>
        </Card>

        <!-- Stats Cards - Limited to Tenant -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <Card class="p-6 bg-card border shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900/50 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">Meus Usuários</p>
                        <p class="text-2xl font-bold text-foreground">{{ stats.myUsers }}</p>
                    </div>
                </div>
            </Card>

            <Card class="p-6 bg-card border shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">Projetos</p>
                        <p class="text-2xl font-bold text-foreground">{{ stats.projects }}</p>
                    </div>
                </div>
            </Card>

            <Card class="p-6 bg-card border shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900/50 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">Este Mês</p>
                        <p class="text-2xl font-bold text-foreground">{{ stats.thisMonth }}</p>
                    </div>
                </div>
            </Card>

            <Card class="p-6 bg-card border shadow-sm">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-100 dark:bg-orange-900/50 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-muted-foreground">Pendentes</p>
                        <p class="text-2xl font-bold text-foreground">{{ stats.pending }}</p>
                    </div>
                </div>
            </Card>
        </div>

        <!-- Action Cards - Tenant Specific -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <Card class="p-6 bg-card border shadow-sm">
                <CardHeader class="p-0 pb-4">
                    <CardTitle class="text-lg font-semibold text-foreground">Minhas Ações</CardTitle>
                </CardHeader>
                <CardContent class="p-0 space-y-3">
                    <Button class="w-full justify-start" variant="outline">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Gerenciar Usuários
                    </Button>
                    <Button class="w-full justify-start" variant="outline">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Configurações
                    </Button>
                    <Button class="w-full justify-start" variant="outline">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Meus Relatórios
                    </Button>
                </CardContent>
            </Card>

            <Card class="p-6 bg-card border shadow-sm">
                <CardHeader class="p-0 pb-4">
                    <CardTitle class="text-lg font-semibold text-foreground">Atividade Recente</CardTitle>
                </CardHeader>
                <CardContent class="p-0 space-y-3">
                    <div v-for="activity in recentActivity" :key="activity.id"
                        class="flex items-start space-x-3 p-3 bg-muted/50 dark:bg-muted/20 rounded-lg border">
                        <div :class="activity.iconColor" class="p-1 rounded-full">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <circle cx="10" cy="10" r="4" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-foreground">{{ activity.description }}</p>
                            <p class="text-xs text-muted-foreground mt-1">{{ activity.time }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Limitations Notice -->
        <Card class="p-4 bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-800">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mr-2" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-amber-800 dark:text-amber-400">
                    <strong>Nota:</strong> Este painel mostra apenas dados relacionados ao seu tenant.
                    Para acesso completo, entre em contato com o administrador do sistema.
                </p>
            </div>
        </Card>
    </main>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button' 
import useAuth from '../../composables/useAuth'

const { user, attempt } = useAuth()

const stats = ref({
    myUsers: 0,
    projects: 0,
    thisMonth: 0,
    pending: 0
})

const recentActivity = ref([
    {
        id: 1,
        description: 'Novo usuário adicionado: João Silva',
        time: '2 horas atrás',
        iconColor: 'text-green-600'
    },
    {
        id: 2,
        description: 'Configurações atualizadas',
        time: '5 horas atrás',
        iconColor: 'text-blue-600'
    },
    {
        id: 3,
        description: 'Relatório mensal gerado',
        time: '1 dia atrás',
        iconColor: 'text-purple-600'
    },
])

const getStatusColor = (status: string) => {
    switch (status) {
        case 'active': return 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-400'
        case 'inactive': return 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-400'
        case 'canceled': return 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-400'
        default: return 'bg-muted text-muted-foreground'
    }
}

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'active': return 'Ativo'
        case 'inactive': return 'Inativo'
        case 'canceled': return 'Cancelado'
        default: return 'Desconhecido'
    }
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('pt-BR')
}

onMounted(async () => {
    // Load user data
    await attempt()

    // Aqui você faria as chamadas para a API para carregar os dados do tenant
    // Simulando dados para demonstração
    stats.value = {
        myUsers: 8,
        projects: 12,
        thisMonth: 24,
        pending: 3
    }
})
</script>