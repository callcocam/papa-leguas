<template>
    <div class="min-h-screen bg-background text-foreground transition-colors w-full">
        <!-- Header -->
        <header class="bg-card border-b border-border shadow-sm flex " v-if="authenticated">
            <div class="">
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex-1">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-foreground">
                            {{ title || 'Painel de Controle' }}
                        </h1>
                        <span
                            class="ml-2 px-2 py-1 text-xs bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-full">
                            {{ papel || 'Autoridade Total' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <ThemeToggle />
                <span class="text-sm text-muted-foreground">{{ user?.name || 'Admin' }}</span>
                <Button variant="outline" size="sm" @click="logout">
                    Sair
                </Button>
            </div>
        </header>
        <!-- Content -->
        <slot />
    </div>
</template>
<script setup lang="ts">

defineProps<{
    user: any;
    authenticated: boolean;
    logout: () => Promise<void>;
    title?: string;
    description?: string;
    papel?: 'Autoridade Total' | 'Tenant' | 'Usuário';
}>();
</script>