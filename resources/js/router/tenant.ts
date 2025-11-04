import { useGenerateRoute } from '../composables/useGenerateRoute'

const { convertToVueRouterRoutes } = useGenerateRoute()

const tenantRoutes: Array<any> = [
    {
        path: '',
        component: () => import('../views/tenant/App.vue'),
        meta: { guard: 'tenant' },
        redirect: { name: 'TenantDashboard' },
        children: [
            {
                path: '',
                name: 'TenantDashboard',
                component: () => import('../views/tenant/Dashboard.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: 'login',
                name: 'TenantLogin',
                component: () => import('../views/tenant/auth/Login.vue'),
                meta: { requiresAuth: false, requiresGuest: true }
            },
            {
                path: 'profile',
                name: 'TenantProfile',
                component: () => import('../views/tenant/Profile.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: 'settings',
                name: 'TenantSettings',
                component: () => import('../views/tenant/Settings.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: 'help',
                name: 'TenantHelp',
                component: () => import('../views/tenant/Help.vue'),
                meta: { requiresAuth: true }
            },
            // Rotas CRUD geradas automaticamente
            ...convertToVueRouterRoutes(),
        ]
    }
]

export default tenantRoutes