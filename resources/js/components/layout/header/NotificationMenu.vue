<!--
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 -->
<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="sm" class="relative h-9 w-9 rounded-full">
                <Bell class="h-4 w-4" />
                <span v-if="unreadCount > 0" 
                      class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 flex items-center justify-center">
                    <span class="text-xs font-medium text-white">{{ displayCount }}</span>
                </span>
                <span class="sr-only">Notificações</span>
            </Button>
        </DropdownMenuTrigger>
        
        <DropdownMenuContent class="w-80" align="end" :side-offset="4">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="font-semibold text-sm">Notificações</h3>
                <div class="flex items-center gap-2">
                    <span v-if="unreadCount > 0" class="text-xs text-muted-foreground">
                        {{ unreadCount }} nova{{ unreadCount > 1 ? 's' : '' }}
                    </span>
                    <Button 
                        v-if="unreadCount > 0"
                        variant="ghost" 
                        size="sm" 
                        class="h-6 px-2 text-xs"
                        @click="markAllAsRead"
                    >
                        Marcar como lidas
                    </Button>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="max-h-96 overflow-y-auto">
                <div v-if="notifications.length === 0" class="p-6 text-center">
                    <Bell class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                    <p class="text-sm text-muted-foreground">Nenhuma notificação</p>
                </div>
                
                <div v-else class="divide-y">
                    <div 
                        v-for="notification in notifications" 
                        :key="notification.id"
                        class="p-4 hover:bg-accent/50 transition-colors group"
                        :class="{ 'bg-blue-50/50 dark:bg-blue-950/20': !notification.read }"
                    >
                        <div class="flex items-start gap-3">
                            <!-- Icon -->
                            <div :class="getNotificationIconClass(notification.type)" 
                                 class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center">
                                <component :is="getNotificationIcon(notification.type)" class="h-4 w-4" />
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-medium text-foreground truncate">
                                        {{ notification.title }}
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <span v-if="!notification.read" 
                                              class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0">
                                        </span>
                                        <span class="text-xs text-muted-foreground whitespace-nowrap">
                                            {{ formatTime(notification.created_at) }}
                                        </span>
                                    </div>
                                </div>
                                <p class="text-xs text-muted-foreground mt-1 line-clamp-2">
                                    {{ notification.message }}
                                </p>
                                
                                <!-- Action Buttons -->
                                <div class="flex items-center justify-between mt-3">
                                    <div class="flex gap-2">
                                        <Button 
                                            v-if="notification.action_url" 
                                            variant="outline" 
                                            size="sm" 
                                            class="h-6 px-2 text-xs"
                                            @click="goToAction(notification)"
                                        >
                                            Ver detalhes
                                        </Button>
                                    </div>
                                    
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Button
                                            v-if="!notification.read"
                                            variant="ghost"
                                            size="sm"
                                            class="h-6 w-6 p-0 text-muted-foreground hover:text-foreground"
                                            @click.stop="markAsRead(notification.id)"
                                            title="Marcar como lida"
                                        >
                                            <Check class="h-3 w-3" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-6 w-6 p-0 text-muted-foreground hover:text-destructive"
                                            @click.stop="removeNotification(notification.id)"
                                            title="Remover notificação"
                                        >
                                            <X class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div v-if="notifications.length > 0" class="p-4 border-t">
                <Button variant="ghost" class="w-full text-sm" @click="viewAllNotifications">
                    Ver todas as notificações
                    <ExternalLink class="ml-2 h-3 w-3" />
                </Button>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Button } from '@/components/ui/button'
import { 
    Bell, 
    User, 
    AlertCircle, 
    CheckCircle, 
    Info, 
    ExternalLink,
    Settings,
    Users,
    Building,
    Check,
    X
} from 'lucide-vue-next'

interface Notification {
    id: string
    title: string
    message: string
    type: 'info' | 'success' | 'warning' | 'error' | 'user' | 'system' | 'tenant' | 'team'
    read: boolean
    created_at: string
    action_url?: string
}

const router = useRouter()
const notifications = ref<Notification[]>([])

// Computed properties
const unreadCount = computed(() => 
    notifications.value.filter(n => !n.read).length
)

const displayCount = computed(() => 
    unreadCount.value > 99 ? '99+' : unreadCount.value.toString()
)

// Notification icon mapping
const getNotificationIcon = (type: string) => {
    const iconMap = {
        'info': Info,
        'success': CheckCircle,
        'warning': AlertCircle,
        'error': AlertCircle,
        'user': User,
        'system': Settings,
        'tenant': Building,
        'team': Users
    }
    return iconMap[type as keyof typeof iconMap] || Info
}

// Notification icon styling
const getNotificationIconClass = (type: string) => {
    const classMap = {
        'info': 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
        'success': 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400',
        'warning': 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400',
        'error': 'bg-red-100 text-red-600 dark:bg-red-900/20 dark:text-red-400',
        'user': 'bg-purple-100 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
        'system': 'bg-gray-100 text-gray-600 dark:bg-gray-900/20 dark:text-gray-400',
        'tenant': 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400',
        'team': 'bg-orange-100 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400'
    }
    return classMap[type as keyof typeof classMap] || classMap.info
}

// Time formatting
const formatTime = (dateString: string) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60))
    
    if (diffInMinutes < 1) return 'Agora'
    if (diffInMinutes < 60) return `${diffInMinutes}m`
    if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h`
    return `${Math.floor(diffInMinutes / 1440)}d`
}

// Actions
const markAsRead = async (notificationId: string) => {
    const notification = notifications.value.find(n => n.id === notificationId)
    if (notification) {
        notification.read = true
        // TODO: Call API to mark as read
    }
}

const goToAction = (notification: Notification) => {
    markAsRead(notification.id)
    if (notification.action_url) {
        router.push(notification.action_url)
    }
}

const removeNotification = async (notificationId: string) => {
    const index = notifications.value.findIndex(n => n.id === notificationId)
    if (index > -1) {
        notifications.value.splice(index, 1)
        // TODO: Call API to remove notification
    }
}

const markAllAsRead = async () => {
    notifications.value.forEach(n => n.read = true)
    // TODO: Call API to mark all as read
}

const viewAllNotifications = () => {
    router.push('/notifications')
}

const fetchNotifications = async () => {
    // TODO: Fetch from API
    // Mock data for now
    notifications.value = [
        {
            id: '1',
            title: 'Novo usuário cadastrado',
            message: 'João Silva se cadastrou no sistema e aguarda aprovação.',
            type: 'user',
            read: false,
            created_at: new Date(Date.now() - 5 * 60 * 1000).toISOString(),
            action_url: '/admin/users'
        },
        {
            id: '2',
            title: 'Backup concluído',
            message: 'O backup automático foi realizado com sucesso.',
            type: 'success',
            read: false,
            created_at: new Date(Date.now() - 30 * 60 * 1000).toISOString()
        },
        {
            id: '3',
            title: 'Tenant criado',
            message: 'Novo tenant "Empresa Alpha" foi criado e está ativo.',
            type: 'tenant',
            read: true,
            created_at: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
            action_url: '/landlord/tenants'
        },
        {
            id: '4',
            title: 'Atualização disponível',
            message: 'Uma nova versão do sistema está disponível.',
            type: 'info',
            read: true,
            created_at: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString()
        }
    ]
}

onMounted(() => {
    fetchNotifications()
})
</script>