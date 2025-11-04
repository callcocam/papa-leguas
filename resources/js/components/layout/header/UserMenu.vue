<!--
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 -->
<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="relative h-10 w-10 rounded-full hover:bg-muted/80">
                <Avatar class="h-10 w-10">
                    <AvatarImage 
                        :src="user.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'User')}&background=3b82f6&color=ffffff`" 
                        :alt="user.name || 'User'" 
                    />
                    <AvatarFallback class="bg-primary text-primary-foreground">
                        {{ userInitials }}
                    </AvatarFallback>
                </Avatar>
            </Button>
        </DropdownMenuTrigger>
        
        <DropdownMenuContent class="w-80" align="end" :side-offset="4">
            <!-- User Info Section -->
            <div class="flex items-center space-x-3 p-4 border-b">
                <Avatar class="h-12 w-12">
                    <AvatarImage 
                        :src="user.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'User')}&background=3b82f6&color=ffffff`" 
                        :alt="user.name || 'User'" 
                    />
                    <AvatarFallback class="bg-primary text-primary-foreground text-lg">
                        {{ userInitials }}
                    </AvatarFallback>
                </Avatar>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-foreground truncate">
                        {{ user.name || 'Usuário' }}
                    </p>
                    <p class="text-xs text-muted-foreground truncate">
                        {{ user.email || 'email@exemplo.com' }}
                    </p>
                    <p v-if="user.tenant?.name" class="text-xs text-blue-600 dark:text-blue-400 truncate">
                        {{ user.tenant.name }}
                    </p>
                </div>
                <div v-if="contextLabel" class="flex-shrink-0">
                    <span :class="contextBadgeClass" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                        {{ contextLabel }}
                    </span>
                </div>
            </div>

            <!-- Navigation Items -->
            <DropdownMenuGroup>
                <DropdownMenuItem 
                    v-for="item in menuItems" 
                    :key="item.href"
                    as-child
                    class="cursor-pointer"
                >
                    <router-link :to="item.href" class="flex items-center space-x-3 px-3 py-2">
                        <component :is="item.icon" class="h-4 w-4 text-muted-foreground" />
                        <span class="text-sm">{{ item.text }}</span>
                        <DropdownMenuShortcut v-if="item.shortcut">{{ item.shortcut }}</DropdownMenuShortcut>
                    </router-link>
                </DropdownMenuItem>
            </DropdownMenuGroup>

            <DropdownMenuSeparator v-if="guardSpecificItems && guardSpecificItems.length"/>

            <!-- Context Specific Items -->
            <DropdownMenuGroup v-if="guardSpecificItems && guardSpecificItems.length">
                <DropdownMenuLabel class="text-xs text-muted-foreground uppercase tracking-wider">
                    {{ contextLabel || 'Menu' }}
                </DropdownMenuLabel>
                <DropdownMenuItem 
                    v-for="item in guardSpecificItems" 
                    :key="item.href"
                    as-child
                    class="cursor-pointer"
                >
                    <router-link :to="item.href" class="flex items-center space-x-3 px-3 py-2">
                        <component :is="item.icon" class="h-4 w-4 text-muted-foreground" />
                        <span class="text-sm">{{ item.text }}</span>
                    </router-link>
                </DropdownMenuItem>
            </DropdownMenuGroup>

            <DropdownMenuSeparator />

            <!-- Logout -->
            <DropdownMenuItem 
                class="cursor-pointer text-red-600 dark:text-red-400 focus:text-red-600 focus:bg-red-50 dark:focus:bg-red-950/50"
                @click="handleSignOut"
            >
                <LogOut class="h-4 w-4 mr-3" />
                <span class="text-sm">Sair</span>
                <DropdownMenuShortcut>⇧⌘Q</DropdownMenuShortcut>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { 
    DropdownMenu, 
    DropdownMenuContent, 
    DropdownMenuItem, 
    DropdownMenuTrigger,
    DropdownMenuGroup,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuShortcut
} from '@/components/ui/dropdown-menu'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { 
    User, 
    Settings, 
    HelpCircle, 
    LogOut
} from 'lucide-vue-next'
import useAuth from './../../../composables/useAuth'

interface Props {
    user: {
        name?: string
        email?: string
        avatar_url?: string
        tenant?: {
            name?: string
        }
    },
    guardSpecificItems?: Array<any>
}

const props = defineProps<Props>()
const router = useRouter()
const { logout, guard } = useAuth()

// Computed for user initials
const userInitials = computed(() => {
    const name = props.user.name || 'User'
    return name.split(' ')
        .map(word => word.charAt(0))
        .slice(0, 2)
        .join('')
        .toUpperCase()
})

// Common menu items
const menuItems = computed(() => [
    { href: '/profile', icon: User, text: 'Perfil', shortcut: '⌘P' },
    { href: '/settings', icon: Settings, text: 'Configurações', shortcut: '⌘,' },
    { href: '/help', icon: HelpCircle, text: 'Ajuda & Suporte' },
])

// Context-specific menu items (baseado no domínio, não em guard)

// Context badge (baseado no guard que vem do useAuth)
const contextBadgeClass = computed(() => {
    const classes = {
        'landlord': 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
        'tenant': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    }
    return classes[guard.value as keyof typeof classes] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
})

// Context label
const contextLabel = computed(() => {
    const labels = {
        'landlord': 'Admin',
        'tenant': 'Tenant',
    }
    return labels[guard.value as keyof typeof labels] || null
})

// Sign out handler
const handleSignOut = async () => {
    try {
        await logout(guard.value)
        router.push('/')
    } catch (error) {
        console.error('Erro ao fazer logout:', error)
    }
}
</script>
