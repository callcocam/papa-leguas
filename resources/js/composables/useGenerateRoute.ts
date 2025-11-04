import type { RouteRecordRaw } from 'vue-router'

interface RouteData {
    redirect: any
    path: string
    name: string
    component: string
    meta: {
        title: string
        icon: string
        action: string
        requiresAuth?: boolean
        resource?: string
    }
    children?: RouteData[]
}

interface ResourceData {
    resource: string
    label: string
    icon: string
    controller: string
    routes: RouteData
}

// Pré-registra todos os componentes usando import.meta.glob
const viewModules = import.meta.glob('../views/**/*.vue')

export function useGenerateRoute() {
    const routesData: ResourceData[] = (window as any).Laravel?.routes || []  
    
    // Função para resolver o caminho do componente
    const resolveComponent = (componentPath: string) => {
        // Remove o prefixo 'views/' se existir
        const normalizedPath = componentPath.startsWith('views/') 
            ? componentPath.replace('views/', '../views/')
            : componentPath.startsWith('../views/')
                ? componentPath
                : `../views/${componentPath}`
         
        
        // Procura o módulo exato
        if (viewModules[normalizedPath]) { 
            return viewModules[normalizedPath]
        }
        
        // Se não encontrou, procura com variações
        const possiblePaths = [
            normalizedPath,
            normalizedPath.endsWith('.vue') ? normalizedPath : `${normalizedPath}.vue`,
            `../views/${componentPath}`,
            `../views/${componentPath}.vue`
        ]
        
        for (const path of possiblePaths) {
            if (viewModules[path]) { 
                return viewModules[path]
            }
        }
         
        
        // Retorna um componente de fallback
        return () => Promise.resolve({ default: { template: '<div>Componente não encontrado</div>' } })
    }
    
    // Função para converter rotas do Laravel para formato Vue Router
    const convertToVueRouterRoutes = (): RouteRecordRaw[] => {
        const routes: RouteRecordRaw[] = []
        
        const processRoute = (route: RouteData, resourceData: ResourceData): RouteRecordRaw => { 
            
            const vueRoute: RouteRecordRaw = {
                path: route.path,
                name: route.name,
                component: resolveComponent(route.component),
                meta: {
                    ...route.meta,
                    resource: resourceData.resource,
                    controller: resourceData.controller,
                    resourceLabel: resourceData.label,
                    resourceIcon: resourceData.icon
                }
            }

            if (route.redirect) {
                vueRoute.redirect = route.redirect 
            }
            
            // Se a rota tem filhas, processa recursivamente
            if (route.children && Array.isArray(route.children)) {
                (vueRoute as any).children = route.children.map(child => processRoute(child, resourceData))
            }
            
            return vueRoute
        }
        
        routesData.forEach((resourceData: ResourceData) => { 
            if (resourceData.routes) {
                const vueRoute = processRoute(resourceData.routes, resourceData)
                routes.push(vueRoute)
            }
        }) 
        return routes
    }
    
    // Função para obter rotas de um resource específico
    const getRoutesByResource = (resourceName: string): RouteData[] => {
        const resource = routesData.find(r => r.resource === resourceName)
        if (!resource || !resource.routes) return []
        
        const allRoutes: RouteData[] = [resource.routes]
        if (resource.routes.children) {
            allRoutes.push(...resource.routes.children)
        }
        return allRoutes
    }
    
    // Função para obter uma rota específica por nome (busca recursiva)
    const findRouteByName = (route: RouteData, routeName: string): RouteData | undefined => {
        if (route.name === routeName) return route
        
        if (route.children) {
            for (const child of route.children) {
                const found = findRouteByName(child, routeName)
                if (found) return found
            }
        }
        
        return undefined
    }
    
    const getRouteByName = (routeName: string): RouteData | undefined => {
        for (const resource of routesData) {
            if (resource.routes) {
                const found = findRouteByName(resource.routes, routeName)
                if (found) return found
            }
        }
        return undefined
    }
    
    // Função para obter a rota index de um resource
    const getIndexRoute = (resourceName: string): RouteData | undefined => {
        const resource = routesData.find(r => r.resource === resourceName)
        return resource?.routes || undefined
    }
    
    // Função para verificar se uma rota existe
    const routeExists = (routeName: string): boolean => {
        return getRouteByName(routeName) !== undefined
    }
    
    // Função para obter todas as rotas disponíveis (busca recursiva)
    const collectAllRoutes = (route: RouteData): RouteData[] => {
        const routes: RouteData[] = [route]
        if (route.children) {
            route.children.forEach(child => {
                routes.push(...collectAllRoutes(child))
            })
        }
        return routes
    }
    
    const getAllRoutes = (): RouteData[] => {
        const allRoutes: RouteData[] = []
        routesData.forEach(resource => {
            if (resource.routes) {
                allRoutes.push(...collectAllRoutes(resource.routes))
            }
        })
        return allRoutes
    }
    
    // Função para obter recursos disponíveis
    const getAvailableResources = (): string[] => {
        return routesData.map(r => r.resource)
    }
    
    // Função para obter a rota principal de um resource
    const getResourceMainRoute = (resourceName: string): RouteData | undefined => {
        const resource = routesData.find(r => r.resource === resourceName)
        return resource?.routes
    }
    
    // Função para obter apenas as rotas filhas de um resource
    const getResourceChildRoutes = (resourceName: string): RouteData[] => {
        const resource = routesData.find(r => r.resource === resourceName)
        return resource?.routes?.children || []
    }
    
    // Função para obter uma rota filha específica
    const getChildRoute = (resourceName: string, action: string): RouteData | undefined => {
        const childRoutes = getResourceChildRoutes(resourceName)
        return childRoutes.find(route => route.meta.action === action)
    }
    
    // Função para verificar se um resource tem rotas filhas
    const hasChildRoutes = (resourceName: string): boolean => {
        const resource = routesData.find(r => r.resource === resourceName)
        return !!(resource?.routes?.children && resource.routes.children.length > 0)
    }
    
    return {
        routesData,
        convertToVueRouterRoutes,
        getRoutesByResource,
        getRouteByName,
        getIndexRoute,
        routeExists,
        getAllRoutes,
        getAvailableResources,
        getResourceMainRoute,
        getResourceChildRoutes,
        getChildRoute,
        hasChildRoutes
    }
}
