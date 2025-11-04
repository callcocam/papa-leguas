import { computed, ref } from 'vue'
import { useApi } from './useApi'
import { useNotifications } from './useNotifications'

interface ActionConfig {
  url: string
  method: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'
  confirm?: {
    title?: string
    message?: string
    confirmText?: string
    cancelText?: string
    confirmColor?: string
    requiresTypedConfirmation?: boolean
    typedConfirmationWord?: string
  }
  successMessage?: string
  errorMessage?: string
  onSuccess?: (data: any) => void
  onError?: (error: any) => void
}

export function useAction() {
  const api = useApi()
  const notifications = useNotifications()
  const isExecuting = ref(false)

  /**
   * Executa uma action com dados de formulário
   */
  const execute = async (
    action: ActionConfig,
    formData?: Record<string, any>
  ): Promise<boolean> => {
    isExecuting.value = true

    try {
      // Verifica se há arquivos no formData
      const hasFiles = formData && Object.values(formData).some(value => value instanceof File)

      let result: any = null 
      if (hasFiles) {
        // Se houver arquivos, cria FormData
        const data = new FormData()

        Object.entries(formData).forEach(([key, value]) => {
          if (value instanceof File) {
            data.append(key, value)
          } else if (Array.isArray(value)) {
            value.forEach(item => {
              if (item instanceof File) {
                data.append(`${key}[]`, item)
              } else {
                data.append(`${key}[]`, String(item))
              }
            })
          } else if (value !== null && value !== undefined) {
            data.append(key, String(value))
          }
        })

        result = await api.upload(action.url, data, {
          successMessage: action.successMessage
        })
      } else {
        // Sem arquivos, usa método HTTP apropriado
        console.log('Executing action:', action.method, action.url)
        
        switch (action.method) {
          case 'GET':
            result = await api.get(action.url, formData, {
              successMessage: action.successMessage
            })
            break
          case 'POST':
            result = await api.post(action.url, formData, {
              successMessage: action.successMessage
            })
            break
          case 'PUT':
            result = await api.put(action.url, formData, {
              successMessage: action.successMessage
            })
            break
          case 'PATCH':
            result = await api.patch(action.url, formData, {
              successMessage: action.successMessage
            })
            break
          case 'DELETE':
            result = await api.del(action.url, formData, {
              successMessage: action.successMessage
            })
            break
        }
      }

      // Verifica se há erro da API
      if (api.error.value !== null) {
        // Erro da API
        console.log('API Error detected:', api.error.value)
        if (action.onError) {
          action.onError(api.error.value)
        }
        return false
      }

      if (result !== null) {
        // Sucesso
        console.log('Action success, result:', result)
        if (action.onSuccess) {
          action.onSuccess(result)
        }
        return true
      } else {
        // Resultado null mas sem erro (caso raro)
        console.log('Result is null but no API error')
        return false
      }
    } catch (e: any) {
      console.error('Action execution error (caught in catch):', e)

      if (action.errorMessage) {
        notifications.error(action.errorMessage)
      } else if (!api.error.value) {
        // Se não há erro da API e nem mensagem customizada, mostra erro genérico
        notifications.error('Ocorreu um erro ao executar a ação.')
      }

      if (action.onError) {
        action.onError(e)
      }

      return false
    } finally {
      isExecuting.value = false
    }
  }

  /**
   * Executa uma action GET simples
   */
  const executeGet = async (url: string, params?: Record<string, any>) => {
    return execute({
      url,
      method: 'GET'
    }, params)
  }

  /**
   * Executa uma action POST simples
   */
  const executePost = async (url: string, data?: Record<string, any>) => {
    return execute({
      url,
      method: 'POST'
    }, data)
  }

  /**
   * Executa uma action DELETE com confirmação
   */
  const executeDelete = async (url: string, confirmed: boolean = false) => {
    if (!confirmed) {
      // Se não confirmado, retorna false para mostrar confirmação
      return false
    }

    return execute({
      url,
      method: 'DELETE',
      successMessage: 'Registro excluído com sucesso!'
    })
  }

  /**
   * Valida se a palavra digitada está correta
   */
  const validateTypedConfirmation = (
    typedWord: string,
    expectedWord: string
  ): boolean => {
    return typedWord.toUpperCase().trim() === expectedWord.toUpperCase().trim()
  }

  /**
   * Verifica se uma action requer confirmação por digitação
   */
  const requiresTypedConfirmation = (action: ActionConfig): boolean => {
    return action.confirm?.requiresTypedConfirmation === true
  }

  /**
   * Obtém a palavra esperada para confirmação
   */
  const getTypedConfirmationWord = (action: ActionConfig): string => {
    return action.confirm?.typedConfirmationWord || 'EXCLUIR'
  }

  return {
    execute,
    executeGet,
    executePost,
    executeDelete,
    validateTypedConfirmation,
    requiresTypedConfirmation,
    getTypedConfirmationWord,
    isExecuting: computed(() => isExecuting.value || api.loading.value),
    error: api.error,
    data: api.data
  }
}
