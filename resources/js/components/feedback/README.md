# Sistema de Feedback e Notificações

Sistema completo de feedback visual e notificações para a aplicação, incluindo toasts, notificações persistentes, loading global e suporte a notificações em tempo real.

## 📦 Componentes Criados

### Composables

1. **`useNotifications`** - Gerenciamento global de notificações
2. **`useGlobalLoading`** - Controle de loading e progress global
3. **`useRealtimeNotifications`** - Notificações em tempo real (Pusher/Echo/WebSocket)

### Componentes Vue

1. **`GlobalLoadingBar`** - Barra de progresso no topo da página
2. **`NotificationCenter`** - Centro de notificações (sino com lista)

## 🚀 Como Usar

### 1. Configuração Inicial

Adicione os componentes ao layout principal da aplicação:

```vue
<template>
  <div>
    <!-- Barra de loading global -->
    <GlobalLoadingBar />

    <!-- Seu conteúdo -->
    <router-view />

    <!-- Sonner Toaster (já deve existir) -->
    <Toaster />
  </div>
</template>

<script setup>
import GlobalLoadingBar from '@/components/feedback/GlobalLoadingBar.vue'
import { Toaster } from 'vue-sonner'
</script>
```

Adicione o `NotificationCenter` no header/navbar:

```vue
<template>
  <header>
    <!-- Outros itens do header -->

    <!-- Centro de notificações -->
    <NotificationCenter />
  </header>
</template>

<script setup>
import NotificationCenter from '@/components/feedback/NotificationCenter.vue'
</script>
```

### 2. Usando Notificações (Toasts)

```typescript
import { useNotifications } from '@/composables/useNotifications'

const notifications = useNotifications()

// Toast de sucesso
notifications.success('Operação realizada com sucesso!')

// Toast de erro
notifications.error('Ocorreu um erro')

// Toast de aviso
notifications.warning('Atenção: verifique os dados')

// Toast de informação
notifications.info('Você tem uma nova mensagem')

// Toast com ação
notifications.success('Arquivo salvo!', {
  action: {
    label: 'Abrir',
    onClick: () => {
      // Ação ao clicar
    }
  }
})

// Promise toast (loading automático)
notifications.promise(
  fetchData(),
  {
    loading: 'Carregando dados...',
    success: 'Dados carregados!',
    error: 'Falha ao carregar'
  }
)
```

### 3. Usando Loading Global

```typescript
import { useGlobalLoading } from '@/composables/useGlobalLoading'

const loading = useGlobalLoading()

// Iniciar loading
const taskId = loading.start('Processando...')

// Atualizar progresso (0-100)
loading.updateProgress(taskId, 50, 'Metade concluída')

// Finalizar
loading.finish(taskId)

// Wrapper automático
await loading.withLoading(async (updateProgress) => {
  updateProgress(25, 'Etapa 1...')
  await step1()

  updateProgress(50, 'Etapa 2...')
  await step2()

  updateProgress(75, 'Etapa 3...')
  await step3()

  updateProgress(100, 'Concluído!')
}, 'Processando dados')
```

### 4. Centro de Notificações

O componente `NotificationCenter` é automático. Todas as notificações criadas via `useNotifications` aparecerão lá.

```typescript
import { useNotifications } from '@/composables/useNotifications'

const notifications = useNotifications()

// Ver todas as notificações
console.log(notifications.notifications.value)

// Ver quantidade não lidas
console.log(notifications.unreadCount.value)

// Marcar como lida
notifications.markAsRead(notificationId)

// Marcar todas como lidas
notifications.markAllAsRead()

// Remover notificação
notifications.removeNotification(notificationId)

// Limpar todas
notifications.clearAll()
```

### 5. Notificações em Tempo Real

#### Configuração com Pusher

```typescript
import { useRealtimeNotifications } from '@/composables/useRealtimeNotifications'

// No componente raiz ou App.vue
const realtime = useRealtimeNotifications({
  enabled: true,
  driver: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER
})

// Conectar manualmente se necessário
realtime.connect()
```

#### Configuração com Laravel Echo

```typescript
const realtime = useRealtimeNotifications({
  enabled: true,
  driver: 'echo'
})
```

#### Configuração com WebSocket Nativo

```typescript
const realtime = useRealtimeNotifications({
  enabled: true,
  driver: 'websocket',
  wsHost: 'localhost',
  wsPort: 6001
})
```

#### Backend (Laravel)

```php
// Broadcast de notificação
broadcast(new \App\Notifications\NewMessageNotification($user, $message));

// OU via evento
event(new \App\Events\NotificationReceived([
    'type' => 'info',
    'title' => 'Nova Mensagem',
    'message' => 'Você recebeu uma nova mensagem',
    'action' => [
        'label' => 'Ver Mensagem',
        'url' => '/messages/123'
    ]
]));
```

## 🔧 Integração Automática

O sistema já está integrado com:

- ✅ `useApi` - Todos os métodos HTTP já usam o novo sistema
- ✅ `useAction` - Actions já mostram notificações automaticamente
- ✅ `localStorage` - Notificações são persistidas automaticamente

## 📱 Funcionalidades

### Toasts (vue-sonner)

- ✅ Toast de sucesso/erro/aviso/info
- ✅ Ações customizadas nos toasts
- ✅ Promise toasts (loading automático)
- ✅ Posicionamento configurável
- ✅ Animações suaves

### Centro de Notificações

- ✅ Lista de todas as notificações
- ✅ Badge com contador de não lidas
- ✅ Marcar como lida/não lida
- ✅ Remover notificações
- ✅ Limpar todas
- ✅ Ações inline nas notificações
- ✅ Timestamp relativo (ex: "há 5 minutos")
- ✅ Persistência em localStorage

### Loading Global

- ✅ Barra de progresso no topo
- ✅ Suporte a múltiplas tarefas simultâneas
- ✅ Progresso determinado (0-100%)
- ✅ Progresso indeterminado (animação)
- ✅ Mensagens de loading
- ✅ Wrapper automático com `withLoading`

### Notificações em Tempo Real

- ✅ Suporte a Pusher
- ✅ Suporte a Laravel Echo
- ✅ Suporte a WebSocket nativo
- ✅ Auto-reconexão
- ✅ Integração automática com centro de notificações

## 🎨 Customização

### Cores dos Toasts

As cores já seguem o tema do Tailwind/shadcn-vue:

- Success: Verde
- Error: Vermelho
- Warning: Amarelo
- Info: Azul

### Ícones

Usa Lucide Icons integrado ao projeto:

- Success: `CheckCircle2`
- Error: `XCircle`
- Warning: `AlertTriangle`
- Info: `Info`

## 📝 Exemplos Práticos

### Exemplo 1: Formulário com Loading e Feedback

```typescript
const handleSubmit = async () => {
  const loading = useGlobalLoading()
  const notifications = useNotifications()

  const taskId = loading.start('Salvando dados...')

  try {
    const response = await api.post('/users', formData)

    loading.finish(taskId)
    notifications.success('Usuário criado com sucesso!')

    router.push('/users')
  } catch (error) {
    loading.finish(taskId)
    notifications.error('Erro ao criar usuário')
  }
}
```

### Exemplo 2: Upload com Progresso

```typescript
const uploadFile = async (file: File) => {
  const loading = useGlobalLoading()

  await loading.withLoading(async (updateProgress) => {
    const formData = new FormData()
    formData.append('file', file)

    await axios.post('/upload', formData, {
      onUploadProgress: (progressEvent) => {
        const progress = (progressEvent.loaded / progressEvent.total) * 100
        updateProgress(progress, `Enviando ${Math.round(progress)}%`)
      }
    })
  }, 'Fazendo upload...')
}
```

### Exemplo 3: Notificação Complexa

```typescript
notifications.addNotification({
  type: 'success',
  title: 'Exportação Concluída',
  message: '1.245 registros exportados com sucesso',
  action: {
    label: 'Download',
    onClick: () => {
      window.open('/exports/file.xlsx')
    }
  },
  metadata: {
    file: 'users_2024.xlsx',
    records: 1245
  }
})
```

## 🔍 Troubleshooting

### Notificações não aparecem

1. Verifique se o `Toaster` da vue-sonner está no layout
2. Verifique se o `GlobalLoadingBar` está montado

### Loading não funciona

1. Certifique-se de chamar `finish()` após a operação
2. Use `withLoading` para gerenciamento automático

### Notificações em tempo real não funcionam

1. Verifique se o driver está configurado corretamente
2. Verifique se as credenciais (Pusher key, etc.) estão corretas
3. Certifique-se de que o backend está transmitindo corretamente
