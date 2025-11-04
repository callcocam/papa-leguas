<template>
    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 px-4">
        <div class="w-full max-w-md"> 
            <!-- Logo/Brand Section -->
            <div class="text-center mb-8">
                <PapaLeguasLogo size="lg" class="mb-4" />
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ guardTitle }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ guardDescription }}</p>
            </div>

            <!-- Login Card -->
            <Card class="shadow-xl border-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm">
                <CardHeader class="space-y-1 pb-4">
                    <CardTitle class="text-xl font-semibold text-center text-gray-900">
                        Faça seu login
                    </CardTitle>
                    <CardDescription class="text-center text-gray-600">
                        Digite suas credenciais para acessar o sistema
                    </CardDescription>
                </CardHeader>

                <CardContent class="space-y-4">
                    <!-- Se já estiver autenticado -->
                    <div v-if="authenticated" class="text-center space-y-6">
                        <div class="flex items-center justify-center w-20 h-20 mx-auto bg-green-100 rounded-full">
                            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Olá, {{ user?.name }}!</h3>
                            <p class="text-sm text-gray-600 mt-1">Você já está conectado ao sistema</p>
                        </div>
                        <div class="flex flex-col space-y-3">
                            <Button @click="goToDashboard" size="lg" class="w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                Ir para Dashboard
                            </Button>
                            <Button @click="logout" variant="outline" size="lg" class="w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Sair do Sistema
                            </Button>
                        </div>
                    </div>

                    <!-- Formulário de login -->
                    <form v-else @submit.prevent="handleLogin" class="space-y-5">
                        <!-- Email -->
                        <div class="space-y-2">
                            <Label for="email" class="text-sm font-medium text-gray-700">
                                Endereço de email
                            </Label>
                            <Input id="email" v-model="form.email" type="email" placeholder="exemplo@empresa.com"
                                :disabled="loading" :class="{ 'border-red-500 focus:border-red-500': errors.email }"
                                class="h-11" required autocomplete="email" />
                            <p v-if="errors.email" class="text-xs text-red-600 mt-1">
                                {{ errors.email[0] }}
                            </p>
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <Label for="password" class="text-sm font-medium text-gray-700">
                                Senha
                            </Label>
                            <Input id="password" v-model="form.password" type="password" placeholder="••••••••••••"
                                :disabled="loading" :class="{ 'border-red-500 focus:border-red-500': errors.password }"
                                class="h-11" required autocomplete="current-password" />
                            <p v-if="errors.password" class="text-xs text-red-600 mt-1">
                                {{ errors.password[0] }}
                            </p>
                        </div>

                        <!-- Remember me -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <input id="remember" v-model="form.remember" type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary focus:ring-offset-0" />
                                <Label for="remember" class="text-sm text-gray-600">
                                    Lembrar-me
                                </Label>
                            </div>
                            <a href="#" class="text-sm text-primary hover:text-primary/80 font-medium">
                                Esqueceu a senha?
                            </a>
                        </div>

                        <!-- Error geral -->
                        <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-red-600 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm text-red-800">{{ error }}</p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <Button type="submit" :disabled="loading" size="lg" class="w-full h-11 font-medium">
                            <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ loading ? 'Entrando...' : 'Entrar no Sistema' }}
                        </Button>
                    </form>
                </CardContent>

                <CardFooter class="pt-4">
                    <div class="w-full text-center">
                        <p class="text-xs text-gray-500">
                            Ao entrar, você concorda com nossos
                            <a href="#" class="text-primary hover:underline">Termos de Uso</a>
                            e
                            <a href="#" class="text-primary hover:underline">Política de Privacidade</a>
                        </p>
                    </div>
                </CardFooter>
            </Card>

            <!-- Footer -->
            <div class="text-center mt-8">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    © 2024 Papa Leguas. Desenvolvido por
                    <a href="https://sigasmart.com.br" target="_blank" class="text-primary hover:underline font-medium">
                        Siga Smart
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import useAuth from '../../composables/useAuth'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import PapaLeguasLogo from '../common/PapaLeguasLogo.vue' 

const router = useRouter()
const { authenticated, user, login, logout, guard } = useAuth()

// Estados
const loading = ref(false)
const errors = ref<Record<string, string[]>>({})
const error = ref('')

// Formulário
const form = reactive({
    email: '',
    password: '',
    remember: false,
})

// Computados para título e descrição baseados no guard
const guardTitle = computed(() => {
    switch (guard.value) {
        case 'landlord':
            return 'Painel Landlord'
        case 'tenant':
            return 'Painel Tenant'
        default:
            return 'Papa Leguas'
    }
})

const guardDescription = computed(() => {
    switch (guard.value) {
        case 'landlord':
            return 'Administração completa do sistema'
        case 'tenant':
            return 'Gestão da sua empresa'
        default:
            return 'Sistema de gestão empresarial'
    }
})

// Funções
const clearErrors = () => {
    errors.value = {}
    error.value = ''
}

const handleLogin = async () => {
    loading.value = true
    clearErrors()

    try {
        await login(form)

        // Sucesso! Redirecionar para o dashboard apropriado
        await redirectAfterLogin()

    } catch (loginError: any) {
        console.error('Login error:', loginError)

        if (loginError && typeof loginError === 'object') {
            // Erros de validação de campos específicos
            if (loginError.email) {
                errors.value.email = Array.isArray(loginError.email) ? loginError.email : [loginError.email]
            }
            if (loginError.password) {
                errors.value.password = Array.isArray(loginError.password) ? loginError.password : [loginError.password]
            }

            // Se não há erros específicos de campo, mostrar erro geral
            if (!loginError.email && !loginError.password) {
                error.value = 'Credenciais inválidas. Verifique seu email e senha.'
            }
        } else {
            // Erro geral
            error.value = 'Erro ao fazer login. Verifique suas credenciais e tente novamente.'
        }
    } finally {
        loading.value = false
    }
}

const redirectAfterLogin = async () => {
    // Determinar para onde redirecionar baseado no guard
    const currentGuard = guard.value

    let dashboardRoute = 'Home'

    switch (currentGuard) {
        case 'landlord':
            dashboardRoute = 'LandlordDashboard'
            break
        case 'tenant':
            dashboardRoute = 'TenantDashboard'
            break
    }

    console.log(`🚀 Redirecting to dashboard: ${dashboardRoute} (guard: ${currentGuard})`)

    await router.push({ name: dashboardRoute })
}

const goToDashboard = async () => {
    await redirectAfterLogin()
}
</script>