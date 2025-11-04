import { ref } from 'vue'
import type { Ref } from 'vue'
import { useNotifications } from './useNotifications'
import { useGlobalLoading } from './useGlobalLoading'

interface ApiResponse<T = any> {
  data: T
  message?: string
  errors?: Record<string, string[]>
}

interface ApiError {
  message: string
  errors?: Record<string, string[]>
  status?: number
}

interface UseApiOptions {
  showSuccessToast?: boolean
  showErrorToast?: boolean
  successMessage?: string
}

export function useApi<T = any>() {
  const loading = ref(false)
  const error: Ref<ApiError | null> = ref(null)
  const data: Ref<T | null> = ref(null)
  const notifications = useNotifications()
  const globalLoading = useGlobalLoading()

  const handleError = (e: any, showToast: boolean = true): ApiError => {
    let apiError: ApiError = {
      message: 'Ocorreu um erro inesperado',
      status: e.response?.status
    }

    if (e.response) {
      // Erro do servidor
      if (e.response.status === 419) {
        apiError.message = 'Sessão expirada. Por favor, recarregue a página.'
      } else if (e.response.status === 422) {
        // Validation errors
        apiError.message = e.response.data?.message || 'Erro de validação'
        apiError.errors = e.response.data?.errors
      } else if (e.response.status === 403) {
        apiError.message = 'Você não tem permissão para executar esta ação'
      } else if (e.response.status === 404) {
        apiError.message = 'Recurso não encontrado'
      } else if (e.response.data?.message) {
        apiError.message = e.response.data.message
      }
    } else if (e.request) {
      // Request foi feito mas sem resposta
      apiError.message = 'Sem resposta do servidor. Verifique sua conexão.'
    } else {
      // Erro ao configurar request
      apiError.message = e.message || 'Erro ao processar requisição'
    }

    if (showToast) {
      notifications.error(apiError.message)
    }

    return apiError
  }

  const request = async <R = T>(
    method: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE',
    url: string,
    payload?: any,
    options: UseApiOptions = {}
  ): Promise<R | null> => {
    const {
      showSuccessToast = true,
      showErrorToast = true,
      successMessage
    } = options

    loading.value = true
    error.value = null

    try {
      const config: any = {
        method,
        url
      }

      // Para GET/DELETE, payload vai em params
      if (method === 'GET' || method === 'DELETE') {
        if (payload) {
          config.params = payload
        }
      } else {
        // Para POST/PUT/PATCH, payload vai em data
        config.data = payload
      }

      const response = await window.axios.request<ApiResponse<R>>(config)

      data.value = response.data.data || response.data as any

      // Mostra toast de sucesso se configurado
      if (showSuccessToast) {
        const message = successMessage || response.data.message || 'Operação realizada com sucesso!'
        notifications.success(message)
      }

      return response.data.data || response.data as R
    } catch (e: any) {
      error.value = handleError(e, showErrorToast)
      return null
    } finally {
      loading.value = false
    }
  }

  // Métodos convenientes
  const get = <R = T>(url: string, params?: any, options?: UseApiOptions) =>
    request<R>('GET', url, params, { showSuccessToast: false, ...options })

  const post = <R = T>(url: string, payload?: any, options?: UseApiOptions) =>
    request<R>('POST', url, payload, options)

  const put = <R = T>(url: string, payload?: any, options?: UseApiOptions) =>
    request<R>('PUT', url, payload, options)

  const patch = <R = T>(url: string, payload?: any, options?: UseApiOptions) =>
    request<R>('PATCH', url, payload, options)

  const del = <R = T>(url: string, params?: any, options?: UseApiOptions) =>
    request<R>('DELETE', url, params, options)

  // Método especial para upload de arquivos
  const upload = async <R = T>(
    url: string,
    formData: FormData,
    options: UseApiOptions = {}
  ): Promise<R | null> => {
    const {
      showSuccessToast = true,
      showErrorToast = true,
      successMessage
    } = options

    loading.value = true
    error.value = null

    try {
      const response = await window.axios.post<ApiResponse<R>>(url, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })

      data.value = response.data.data || response.data as any

      if (showSuccessToast) {
        const message = successMessage || response.data.message || 'Upload realizado com sucesso!'
        notifications.success(message)
      }

      return response.data.data || response.data as R
    } catch (e: any) {
      error.value = handleError(e, showErrorToast)
      return null
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    data,
    get,
    post,
    put,
    patch,
    del,
    upload
  }
}
