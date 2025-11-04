<!--
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 -->
<template>
    <Card class="p-6 bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm border-0 shadow-lg">
        <CardHeader class="pb-4">
            <CardTitle class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                    {{ userInitials }}
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ user.name }}</h3>
                    <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                </div>
            </CardTitle>
        </CardHeader>
        
        <CardContent class="space-y-4">
            <!-- Tenant Information -->
            <div v-if="user.tenant" class="space-y-2">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Organização</h4>
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <p class="font-medium text-blue-900 dark:text-blue-100">{{ user.tenant.name }}</p>
                    <p v-if="user.tenant.domain" class="text-sm text-blue-700 dark:text-blue-300">{{ user.tenant.domain }}</p>
                    <div v-if="user.tenant.status" class="mt-2">
                        <span :class="getStatusClasses(user.tenant.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                            {{ getStatusLabel(user.tenant.status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- User Details -->
            <div class="space-y-2">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Detalhes do Usuário</h4>
                <div class="space-y-2 text-sm">
                    <div v-if="user.phone" class="flex justify-between">
                        <span class="text-muted-foreground">Telefone:</span>
                        <span class="font-medium">{{ user.phone }}</span>
                    </div>
                    <div v-if="user.current_guard" class="flex justify-between">
                        <span class="text-muted-foreground">Perfil:</span>
                        <span class="font-medium capitalize">{{ user.current_guard }}</span>
                    </div>
                    <div v-if="user.status" class="flex justify-between">
                        <span class="text-muted-foreground">Status:</span>
                        <span :class="getStatusClasses(user.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                            {{ getStatusLabel(user.status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Last Activity -->
            <div v-if="user.last_login_at" class="space-y-2">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Último Acesso</h4>
                <p class="text-sm text-muted-foreground">{{ formatDate(user.last_login_at) }}</p>
            </div>
        </CardContent>
    </Card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from './ui/card'

interface Props {
    user: {
        name: string
        email: string
        phone?: string
        status?: string
        current_guard?: string
        last_login_at?: string
        tenant?: {
            name: string
            domain?: string
            status?: string
        }
    }
}

const props = defineProps<Props>()

const userInitials = computed(() => {
    return props.user.name
        .split(' ')
        .map(name => name.charAt(0))
        .slice(0, 2)
        .join('')
        .toUpperCase()
})

const getStatusClasses = (status: string) => {
    const statusMap: Record<string, string> = {
        'active': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        'inactive': 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
        'published': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        'draft': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
        'canceled': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
    }
    return statusMap[status.toLowerCase()] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const getStatusLabel = (status: string) => {
    const labelMap: Record<string, string> = {
        'active': 'Ativo',
        'inactive': 'Inativo', 
        'published': 'Ativo',
        'draft': 'Rascunho',
        'canceled': 'Cancelado'
    }
    return labelMap[status.toLowerCase()] || status
}

const formatDate = (dateString: string) => {
    try {
        return new Date(dateString).toLocaleString('pt-BR')
    } catch {
        return dateString
    }
}
</script>