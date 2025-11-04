import { ref, onMounted, onUnmounted } from 'vue'
import { useNotifications } from './useNotifications'

interface RealtimeConfig {
  enabled: boolean
  driver: 'pusher' | 'echo' | 'websocket'
  key?: string
  cluster?: string
  wsHost?: string
  wsPort?: number
}

// Estado global de conexão
const isConnected = ref(false)
const connection = ref<any>(null)

export function useRealtimeNotifications(config?: Partial<RealtimeConfig>) {
  const notifications = useNotifications()

  const defaultConfig: RealtimeConfig = {
    enabled: false,
    driver: 'pusher',
    ...config
  }

  /**
   * Conecta ao serviço de notificações em tempo real
   */
  const connect = async () => {
    if (!defaultConfig.enabled) {
      console.warn('Realtime notifications are disabled')
      return
    }

    try {
      if (defaultConfig.driver === 'pusher') {
        await connectPusher()
      } else if (defaultConfig.driver === 'echo') {
        await connectEcho()
      } else if (defaultConfig.driver === 'websocket') {
        await connectWebSocket()
      }

      isConnected.value = true
    } catch (error) {
      console.error('Failed to connect to realtime service:', error)
      isConnected.value = false
    }
  }

  /**
   * Conecta usando Pusher
   */
  const connectPusher = async () => {
    // @ts-ignore
    if (typeof Pusher === 'undefined') {
      throw new Error('Pusher is not loaded. Please include the Pusher script.')
    }

    // @ts-ignore
    connection.value = new Pusher(defaultConfig.key, {
      cluster: defaultConfig.cluster,
    })

    return setupPusherListeners()
  }

  /**
   * Conecta usando Laravel Echo
   */
  const connectEcho = async () => {
    // @ts-ignore
    if (typeof Echo === 'undefined') {
      throw new Error('Laravel Echo is not loaded.')
    }

    // @ts-ignore
    connection.value = window.Echo

    return setupEchoListeners()
  }

  /**
   * Conecta usando WebSocket nativo
   */
  const connectWebSocket = async () => {
    const wsUrl = `ws://${defaultConfig.wsHost}:${defaultConfig.wsPort}`
    connection.value = new WebSocket(wsUrl)

    return setupWebSocketListeners()
  }

  /**
   * Configura listeners para Pusher
   */
  const setupPusherListeners = () => {
    // Exemplo: escutar em um canal privado do usuário
    // const channel = connection.value.subscribe(`private-user.${userId}`)

    // channel.bind('notification', (data: any) => {
    //   handleIncomingNotification(data)
    // })
  }

  /**
   * Configura listeners para Laravel Echo
   */
  const setupEchoListeners = () => {
    // Exemplo: escutar em um canal privado do usuário
    // connection.value.private(`user.${userId}`)
    //   .notification((notification: any) => {
    //     handleIncomingNotification(notification)
    //   })
  }

  /**
   * Configura listeners para WebSocket nativo
   */
  const setupWebSocketListeners = () => {
    connection.value.onmessage = (event: MessageEvent) => {
      try {
        const data = JSON.parse(event.data)
        handleIncomingNotification(data)
      } catch (error) {
        console.error('Failed to parse WebSocket message:', error)
      }
    }

    connection.value.onerror = (error: Event) => {
      console.error('WebSocket error:', error)
      isConnected.value = false
    }

    connection.value.onclose = () => {
      isConnected.value = false
      console.log('WebSocket connection closed')
    }
  }

  /**
   * Processa notificação recebida em tempo real
   */
  const handleIncomingNotification = (data: any) => {
    const notification = notifications.addNotification({
      type: data.type || 'info',
      title: data.title,
      message: data.message,
      action: data.action,
      metadata: data.metadata
    })

    // Mostra toast também
    const toastMethod = notifications.toast[notification.type]
    if (toastMethod) {
      toastMethod(notification.message || notification.title)
    }
  }

  /**
   * Desconecta do serviço
   */
  const disconnect = () => {
    if (connection.value) {
      if (defaultConfig.driver === 'pusher') {
        connection.value.disconnect()
      } else if (defaultConfig.driver === 'websocket') {
        connection.value.close()
      }

      connection.value = null
      isConnected.value = false
    }
  }

  /**
   * Envia uma notificação (para testes ou comunicação bidirecional)
   */
  const send = (channel: string, event: string, data: any) => {
    if (!isConnected.value || !connection.value) {
      console.warn('Not connected to realtime service')
      return
    }

    if (defaultConfig.driver === 'websocket') {
      connection.value.send(JSON.stringify({
        channel,
        event,
        data
      }))
    }
  }

  // Auto-connect se ativado
  onMounted(() => {
    if (defaultConfig.enabled) {
      connect()
    }
  })

  // Auto-disconnect ao desmontar
  onUnmounted(() => {
    disconnect()
  })

  return {
    // Estado
    isConnected,
    connection,

    // Métodos
    connect,
    disconnect,
    send,

    // Para configuração manual de listeners
    setupPusherListeners,
    setupEchoListeners,
    setupWebSocketListeners
  }
}
