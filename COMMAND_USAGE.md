# PapaLeguasCommand - Guia de Uso

## Visão Geral

O comando `papa-leguas:setup` foi completamente reformulado para oferecer uma experiência interativa e completa de configuração inicial do sistema Papa Leguas.

## Mudanças Principais

### 1. Permissões via VueRouteGeneratorService
- **Antes**: Permissões eram criadas diretamente das rotas Laravel
- **Agora**: Usa o `VueRouteGeneratorService` para gerar permissões baseadas nas rotas Vue
- **Vantagens**:
  - Permissões organizadas por contexto (LANDLORD e TENANT)
  - Nomes amigáveis baseados nos labels das rotas
  - Melhor integração com o sistema de rotas Vue

### 2. Modo Fresh (--fresh)
- Permite deletar e recriar todas as tabelas
- Dupla confirmação para segurança
- Trunca: tenants, users, roles, permissions e tabelas pivot

### 3. Criação de Tenants Padrão
- Cria automaticamente:
  - Tenant Landlord (Administração)
  - Tenant Cliente (Área do Cliente)
- Interface interativa para configurar domínios

### 4. Roles Padrão
- Criação automática de 3 roles:
  - **super-admin**: Acesso total (special = 'all-access')
  - **admin**: Administrador com acesso amplo
  - **user**: Usuário padrão do sistema
- Opção de criar roles customizadas

### 5. Opções de Execução Parcial
- `--tenants`: Cria apenas tenants
- `--users`: Cria apenas usuários
- `--roles`: Cria apenas roles
- `--permissions`: Cria apenas permissões

## Exemplos de Uso

### Configuração Completa (Recomendado)
```bash
php artisan papa-leguas:setup
```
Este comando irá:
1. Perguntar se deseja executar configuração completa
2. Criar tenants (landlord + cliente)
3. Criar usuários para os tenants
4. Criar roles padrão (super-admin, admin, user)
5. Gerar permissões para ambos os contextos (LANDLORD e TENANT)

### Modo Fresh (Deletar e Recriar)
```bash
php artisan papa-leguas:setup --fresh
```
**CUIDADO**: Este comando deleta TODOS os dados das tabelas:
- Tenants
- Users
- Roles
- Permissions
- Tabelas pivot (permission_role, role_user)

### Criar Apenas Permissões
```bash
php artisan papa-leguas:setup --permissions
```
Útil para atualizar permissões após adicionar novos controllers ou rotas.

### Criar Apenas Tenants
```bash
php artisan papa-leguas:setup --tenants
```

### Criar Apenas Roles
```bash
php artisan papa-leguas:setup --roles
```

### Criar Apenas Usuários
```bash
php artisan papa-leguas:setup --users
```

## Fluxo Interativo

### 1. Criação de Tenants
```
Deseja criar os tenants padrão (Landlord + Tenant)? (yes/no)
> yes

Qual o domínio base? [localhost]
> meusite.com

✓ Landlord criado: Landlord - Administração
✓ Tenant Cliente criado: Tenant - Área do Cliente
```

### 2. Criação de Usuários
```
Criando usuário

Qual o nome do usuário? [Admin]
> João Silva

Qual o email do usuário? [admin@meusite.com]
> joao@meusite.com

Qual o status do usuário? [published]
> published

✓ Usuário 'João Silva' criado com sucesso.
```

### 3. Criação de Roles
```
Deseja criar as roles padrão (super-admin, admin, user)? (yes/no)
> yes

✓ Role criada: Super Admin (super-admin)
✓ Role criada: Administrador (admin)
✓ Role criada: Usuário (user)

Deseja associar o usuário à role super-admin? (yes/no)
> yes

✓ Usuário associado à role 'Super Admin'!
```

### 4. Criação de Permissões
```
Gerando permissões baseadas nas rotas Vue...

Deseja criar permissões para ambos os contextos (LANDLORD e TENANT)? (yes/no)
> yes

Processando contexto: Landlord
✓ 15 permissões criadas para Landlord

Processando contexto: Tenant
✓ 12 permissões criadas para Tenant

✓ Total de 27 permissões criadas com sucesso!
```

## Estrutura de Permissões

As permissões são geradas com nomes amigáveis baseados nas rotas Vue:

| Rota Vue | Nome da Permissão | Slug |
|----------|------------------|------|
| `users.list` | Listar Usuários | users.list |
| `users.create` | Criar Usuários | users.create |
| `users.edit` | Editar Usuários | users.edit |
| `products.list` | Listar Produtos | products.list |
| `dashboard.index` | Dashboard Index | dashboard.index |

## Benefícios

1. **Interatividade Total**: Todas as configurações são feitas via perguntas interativas
2. **Segurança**: Dupla confirmação para operações destrutivas
3. **Flexibilidade**: Execute apenas as partes que precisa
4. **Organização**: Permissões organizadas por contexto (LANDLORD/TENANT)
5. **Visual**: Feedback visual claro com tabelas e ícones
6. **Idempotência**: Pode rodar múltiplas vezes sem duplicar dados

## Notas Importantes

- O comando verifica se registros já existem antes de criar
- Permissões duplicadas são automaticamente ignoradas
- Roles com mesmo slug não são criadas novamente
- Use `--fresh` apenas em ambiente de desenvolvimento
- As permissões são geradas dinamicamente baseadas nos controllers descobertos

## Troubleshooting

### Erro: "Class VueRouteGeneratorService not found"
Certifique-se de que o service está registrado no container:
```php
// No ServiceProvider
$this->app->singleton(VueRouteGeneratorService::class);
```

### Erro: "No routes found for context"
Verifique se existem controllers nos diretórios:
- `App\Http\Controllers\Api\Landlord`
- `App\Http\Controllers\Api\Tenant`

### Permissões não sendo criadas
- Verifique se os controllers seguem o padrão CRUD
- Certifique-se de que o `VueRouteGeneratorService` está descobrindo os controllers
- Use `--fresh` para limpar e recriar tudo

## Próximos Passos

Após executar o comando com sucesso:

1. Faça login com o usuário criado
2. Verifique as permissões no banco de dados
3. Teste o sistema de roles e permissões
4. Configure permissões específicas para cada role conforme necessário
