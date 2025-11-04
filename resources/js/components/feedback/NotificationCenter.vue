<template>
  <Popover v-model:open="isOpen">
    <PopoverTrigger as-child>
      <Button variant="ghost" size="icon" class="relative">
        <Bell class="h-5 w-5" />
        <span
          v-if="unreadCount > 0"
          class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-destructive text-destructive-foreground text-xs font-medium flex items-center justify-center"
        >
          {{ unreadCount > 9 ? '9+' : unreadCount }}
        </span>
      </Button>
    </PopoverTrigger>

    <PopoverContent class="w-96 p-0" align="end">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b">
        <div class="flex items-center gap-2">
          <h3 class="font-semibold">Notificações</h3>
          <span
            v-if="unreadCount > 0"
            class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded-full"
          >
            {{ unreadCount }} nova{{ unreadCount > 1 ? 's' : '' }}
          </span>
        </div>

        <div class="flex items-center gap-1">
          <Button
            v-if="unreadCount > 0"
            variant="ghost"
            size="sm"
            @click="handleMarkAllAsRead"
          >
            Marcar todas como lidas
          </Button>
          <Button
            v-if="notifications.length > 0"
            variant="ghost"
            size="icon"
            @click="handleClearAll"
          >
            <Trash2 class="h-4 w-4" />
          </Button>
        </div>
      </div>

      <!-- Lista de notificações -->
      <ScrollArea class="h-[400px]">
        <div v-if="notifications.length === 0" class="p-8 text-center text-muted-foreground">
          <Bell class="h-12 w-12 mx-auto mb-3 opacity-50" />
          <p>Nenhuma notificação</p>
        </div>

        <div v-else class="divide-y">
          <div
            v-for="notification in notifications"
            :key="notification.id"
            class="p-4 hover:bg-muted/50 transition-colors cursor-pointer relative"
            :class="{ 'bg-primary/5': !notification.read }"
            @click="handleNotificationClick(notification)"
          >
            <!-- Indicador de não lida -->
            <div
              v-if="!notification.read"
              class="absolute left-2 top-1/2 -translate-y-1/2 h-2 w-2 rounded-full bg-primary"
            />

            <div class="flex items-start gap-3" :class="{ 'ml-4': !notification.read }">
              <!-- Ícone -->
              <div
                class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center"
                :class="iconBgClass(notification.type)"
              >
                <component :is="iconComponent(notification.type)" class="h-5 w-5" />
              </div>

              <!-- Conteúdo -->
              <div class="flex-1 min-w-0">
                <p class="font-medium text-sm">{{ notification.title }}</p>
                <p v-if="notification.message" class="text-sm text-muted-foreground mt-1">
                  {{ notification.message }}
                </p>
                <p class="text-xs text-muted-foreground mt-2">
                  {{ formatTimestamp(notification.timestamp) }}
                </p>

                <!-- Ação -->
                <Button
                  v-if="notification.action"
                  variant="link"
                  size="sm"
                  class="mt-2 p-0 h-auto"
                  @click.stop="handleActionClick(notification)"
                >
                  {{ notification.action.label }}
                </Button>
              </div>

              <!-- Botão de remover -->
              <Button
                variant="ghost"
                size="icon"
                class="flex-shrink-0 h-8 w-8"
                @click.stop="handleRemove(notification.id)"
              >
                <X class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>
      </ScrollArea>
    </PopoverContent>
  </Popover>
</template>

<script setup lang="ts">
import { ref, h } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import { ScrollArea } from '@/components/ui/scroll-area'
import {
  Bell,
  CheckCircle2,
  XCircle,
  AlertTriangle,
  Info,
  X,
  Trash2
} from 'lucide-vue-next'
import { useNotifications } from '../../composables/useNotifications'
import type { Notification } from '../../composables/useNotifications'
import { formatDistanceToNow } from 'date-fns'
import { ptBR } from 'date-fns/locale'

const isOpen = ref(false)
const notificationsComposable = useNotifications()

const notifications = notificationsComposable.notifications
const unreadCount = notificationsComposable.unreadCount

const iconComponent = (type: Notification['type']) => {
  const icons = {
    success: CheckCircle2,
    error: XCircle,
    warning: AlertTriangle,
    info: Info
  }
  return h(icons[type])
}

const iconBgClass = (type: Notification['type']) => {
  const classes = {
    success: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
    error: 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
    warning: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
    info: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'
  }
  return classes[type]
}

const formatTimestamp = (timestamp: Date) => {
  return formatDistanceToNow(timestamp, {
    addSuffix: true,
    locale: ptBR
  })
}

const handleNotificationClick = (notification: Notification) => {
  if (!notification.read) {
    notificationsComposable.markAsRead(notification.id)
  }
}

const handleActionClick = (notification: Notification) => {
  if (notification.action) {
    notification.action.onClick()
    isOpen.value = false
  }
}

const handleRemove = (id: string) => {
  notificationsComposable.removeNotification(id)
}

const handleMarkAllAsRead = () => {
  notificationsComposable.markAllAsRead()
}

const handleClearAll = () => {
  notificationsComposable.clearAll()
}
</script>
