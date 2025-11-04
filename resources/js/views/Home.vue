<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-slate-900 dark:via-slate-800 dark:to-blue-900 flex items-center justify-center p-4">
        <div class="max-w-4xl mx-auto text-center space-y-8">
            <!-- Hero Section -->
            <div class="space-y-6">
                <h1 class="text-4xl md:text-6xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400 bg-clip-text text-transparent">
                    {{ pageTitle }}
                </h1>
                <p class="text-lg md:text-xl text-muted-foreground max-w-2xl mx-auto">
                    {{ pageDescription }}
                </p>
                
                <!-- Welcome message for authenticated users -->
                <div v-if="authenticated && user.name" class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <p class="text-blue-700 dark:text-blue-300">
                        Bem-vindo, <strong>{{ user.name }}</strong>!
                        <span v-if="user.tenant?.name" class="block text-sm mt-1">
                            Tenant: {{ user.tenant.name }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Feature Cards -->
            <div class="grid md:grid-cols-3 gap-6 mt-12">
                <Card class="p-6 hover:shadow-lg dark:hover:shadow-xl transition-shadow bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm border-0 shadow-lg">
                    <div class="space-y-3">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Rápido</h3>
                        <p class="text-sm text-muted-foreground">
                            Performance otimizada com Laravel e Vue.js
                        </p>
                    </div>
                </Card>

                <Card class="p-6 hover:shadow-lg dark:hover:shadow-xl transition-shadow bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm border-0 shadow-lg">
                    <div class="space-y-3">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/50 rounded-lg flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Seguro</h3>
                        <p class="text-sm text-muted-foreground">
                            Multi-tenancy com isolamento completo de dados
                        </p>
                    </div>
                </Card>

                <Card class="p-6 hover:shadow-lg dark:hover:shadow-xl transition-shadow bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm border-0 shadow-lg">
                    <div class="space-y-3">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/50 rounded-lg flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Flexível</h3>
                        <p class="text-sm text-muted-foreground">
                            Configurações personalizáveis para cada tenant
                        </p>
                    </div>
                </Card>
            </div>

            <!-- CTA Section -->
            <div class="space-y-6 mt-12">
                <!-- Show appropriate buttons based on authentication status -->
                <div v-if="!authenticated" class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <Button size="lg" class="px-8 shadow-lg">
                        <router-link to="/landlord/login" class="flex items-center gap-2">
                            Acesso Landlord
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 12h12"/>
                            </svg>
                        </router-link>
                    </Button>
                    
                    <Button variant="outline" size="lg" class="px-8 shadow-lg bg-white/10 dark:bg-slate-800/50 backdrop-blur-sm">
                        <router-link to="/admin/login" class="flex items-center gap-2">
                            Acesso Tenant
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </router-link>
                    </Button>
                </div>

                <!-- Show dashboard access for authenticated users -->
                <div v-else class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <Button size="lg" class="px-8 shadow-lg">
                        <router-link :to="dashboardRoute" class="flex items-center gap-2">
                            Ir para Dashboard
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </router-link>
                    </Button>
                    
                    <Button variant="outline" size="lg" class="px-8 shadow-lg bg-white/10 dark:bg-slate-800/50 backdrop-blur-sm" @click="handleLogout">
                        <span class="flex items-center gap-2">
                            Sair
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </span>
                    </Button>
                </div>
                
                <p class="text-sm text-muted-foreground">
                    Desenvolvido por 
                    <a href="https://sigasmart.com.br" target="_blank" class="text-primary hover:underline font-medium">
                        Siga Smart
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Card } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import useAuth from './../composables/useAuth'

const { authenticated, user, logout, attempt, guard } = useAuth()
const router = useRouter()

// Computed properties for dynamic content
const pageTitle = computed(() => {
    if (authenticated.value && user.value.tenant?.name) {
        return user.value.tenant.name
    }
    return 'Papa Leguas'
})

const pageDescription = computed(() => {
    if (authenticated.value && user.value.tenant?.description) {
        return user.value.tenant.description
    }
    if (authenticated.value && guard.value === 'landlord') {
        return 'Painel administrativo para gerenciar todos os tenants da plataforma'
    }
    return 'Plataforma multi-tenant completa para gerenciar seus negócios com eficiência e segurança'
})

const dashboardRoute = computed(() => {
    if (guard.value === 'landlord') {
        return '/landlord/dashboard'
    }
    return '/admin/dashboard'
})

const handleLogout = async () => {
    try {
        await logout(guard.value)
        router.push('/')
    } catch (error) {
        console.error('Erro ao fazer logout:', error)
    }
}

// Check authentication status on mount
onMounted(async () => {
    if (!authenticated.value) {
        try {
            await attempt()
        } catch (error) {
            // User not authenticated, which is fine for home page
        }
    }
})
</script>