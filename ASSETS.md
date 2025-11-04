# Papa Leguas - Asset Publishing Guide

## Como publicar e usar os assets

### 1. Instalar o pacote na aplicação Laravel

```bash
composer require callcocam/papa-leguas
```

### 2. Publicar os assets compilados

```bash
# Publicar todos os assets do pacote
php artisan vendor:publish --tag="papa-leguas-assets"

# Ou publicar usando o tag padrão
php artisan vendor:publish --tag="assets"

# Ou forçar a republica��ão (sobrescrever arquivos existentes)
php artisan vendor:publish --tag="papa-leguas-assets" --force
```

### 3. Usar os assets na aplicação

#### Opção 1: Incluir a view do pacote
```blade
@include('papa-leguas::app')
```

#### Opção 2: Incluir manualmente no seu template
```blade
@php
    $manifestPath = public_path('vendor/papa-leguas/manifest.json');
    $manifest = [];
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
    }
    
    $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
    $jsFile = $manifest['resources/js/app.ts']['file'] ?? null;
@endphp

@if($cssFile)
    <link rel="stylesheet" href="{{ asset('vendor/papa-leguas/' . $cssFile) }}">
@endif

@if($jsFile)
    <script src="{{ asset('vendor/papa-leguas/' . $jsFile) }}" defer></script>
@endif
```

### 4. Estrutura dos assets publicados

Após a publicação, os assets estarão disponíveis em:
```
public/vendor/papa-leguas/
├── manifest.json
└── assets/
    ├── app-[hash].css
    └── app-[hash].js
```

### 5. Desenvolvimento

Para recompilar os assets durante o desenvolvimento:

```bash
# No diretório do pacote
npm run dev    # Modo desenvolvimento
npm run build  # Build para produção
```

Após recompilar, execute novamente o comando de publicação para atualizar os assets na aplicação:

```bash
php artisan vendor:publish --tag="papa-leguas-assets" --force
```