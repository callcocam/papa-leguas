import { computed, reactive } from 'vue'
import { useRouter } from 'vue-router'

const state = reactive({
    authenticated: false,
    user: {}
})

export default function useAuth() {
    const authenticated = computed<boolean>(() => state.authenticated)
    const router = useRouter()
    const user = computed<any>(() => state.user)
    const setAuthenticated = (authenticated: boolean) => {
        state.authenticated = authenticated
    }

    const guard = computed<string>(() => {
        const subdomain = window.location.hostname
        if (subdomain.startsWith('landlord.')) {
            return 'landlord'
        } else {
            return 'tenant'
        }
    })



    const setUser = (user: object) => {
        state.user = user
    }

    const login = async (credentials: object) => {
        try {
            // Primeiro, obtém o cookie CSRF do Sanctum
            await window.axios.get('/sanctum/csrf-cookie')

            // Aguarda um pouco para garantir que o cookie foi setado
            await new Promise(resolve => setTimeout(resolve, 100))

            // Faz o login
            await window.axios.post(`/login`, credentials)

            // Tenta obter os dados do usuário
            return await attempt()
        } catch (e: any) {
            console.error('Login error:', e)

            // Trata diferentes tipos de erro
            if (e.response) {
                if (e.response.status === 419) {
                    // CSRF token mismatch - tenta novamente
                    return Promise.reject({
                        error: 'Sessão expirada. Por favor, tente novamente.'
                    })
                }

                if (e.response.data?.errors) {
                    return Promise.reject(e.response.data.errors)
                }

                if (e.response.data?.message) {
                    return Promise.reject({
                        error: e.response.data.message
                    })
                }
            }

            return Promise.reject({
                error: 'Erro ao fazer login. Verifique suas credenciais.'
            })
        }
    }

    const logout = async (guard: string = 'web') => {
        await window.axios.post(`/logout`, { guard })
        setAuthenticated(false)
        setUser({})
        const routeLogin = guard === 'landlord' ? 'LandlordLogin' : 'TenantLogin'
        
       setTimeout(() => {
            router.push({ name: routeLogin })
       }, 100)
    }

    const attempt = async () => {
        try {
            const path: string = guard.value
            let response: any = await window.axios.get(`/api/user`, { params: { guard: path } })
            setAuthenticated(true)
            setUser(response.data)

            return response
        } catch (e) {
            setAuthenticated(false)
            setUser({})
        }
    }

    return {
        authenticated,
        user,
        login,
        attempt,
        logout,
        guard
    }
}