import { useGenerateRoute } from '../composables/useGenerateRoute'

const { convertToVueRouterRoutes } = useGenerateRoute()

const landlordRoutes: Array<any> = [
    {
        path: '',
        component: () => import('../views/landlord/App.vue'),
        meta: { guard: 'landlord' },
        redirect: { name: 'LandlordDashboard' },
        children: [
            {
                path: '',
                name: 'LandlordDashboard',
                component: () => import('../views/landlord/Dashboard.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: 'login',
                name: 'LandlordLogin',
                component: () => import('../views/landlord/auth/Login.vue'),
                meta: { requiresAuth: false, requiresGuest: true }
            },
            {
                path: 'profile',
                name: 'LandlordProfile',
                component: () => import('../views/landlord/Profile.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: 'settings',
                name: 'LandlordSettings',
                component: () => import('../views/landlord/Settings.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: 'help',
                name: 'LandlordHelp',
                component: () => import('../views/landlord/Help.vue'),
                meta: { requiresAuth: true }
            },
            // Rotas CRUD geradas automaticamente
            ...convertToVueRouterRoutes(),
        ]
    }
]

export default landlordRoutes