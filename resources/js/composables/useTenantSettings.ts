import { reactive, computed } from 'vue'

interface TenantSettings {
    id?: number
    name?: string
    slug?: string
    domain?: string
    database?: string
    prefix?: string
    email?: string
    phone?: string
    document?: string
    settings?: Record<string, any>
    status?: string
    is_primary?: boolean
    description?: string
    created_at?: string
    updated_at?: string
    deleted_at?: string
    data?: any
}

interface TenantSettingsState {
    tenantSettings: TenantSettings
    loading: boolean
    error: string | null
}

const state = reactive<TenantSettingsState>({
    tenantSettings: {},
    loading: false,
    error: null
})

export default function useTenantSettings() {
    const tenantSettings = computed<TenantSettings>(() => state.tenantSettings)
    const loading = computed<boolean>(() => state.loading)
    const error = computed<string | null>(() => state.error)

    const fetchTenantSettings = async (): Promise<void> => {
        state.loading = true
        state.error = null

        try {
            const response = await window.axios.get('/api/tenant-settings')
            state.tenantSettings = response.data
        } catch (e: any) {
            const errorMessage = e.response?.data?.message || 'Erro ao carregar informações do tenant'
            state.error = errorMessage
            console.error('Error fetching tenant settings:', e)
        } finally {
            state.loading = false
        }
    }

    const clearError = (): void => {
        state.error = null
    }

    return {
        tenantSettings,
        loading,
        error,
        fetchTenantSettings,
        clearError
    }
}
