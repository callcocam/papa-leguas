import { createRouter, createWebHistory } from "vue-router";
import tenantRoutes from "./tenant";
import landlordRoutes from "./landlord";
import { siteRoutes } from "./site";
import useAuth from "../composables/useAuth";

// Definindo as rotas principais e importando as rotas específicas de cada contexto
const getGuardFromRoutes = (): Array<any> | null => {
    const hostname = window.location.hostname; // Remove www. se existir    
    // Configurações
    const config = {
        // Domínio base sem protocolo nem www
        baseDomain: window.Laravel.baseDomain,
        localDomains: window.Laravel?.localDomains || ['localhost', '127.0.0.1'],
    };

    // Se for localhost
    if (config.localDomains.includes(hostname)) {
        return siteRoutes;
    }

    // Remove www. se existir
    const cleanHostname = hostname.replace(/^www\./, '');

    // Se for exatamente o domínio base = site principal
    if (hostname === config.baseDomain) {
        return siteRoutes;
    }

    // Verifica se tem subdomínio
    if (cleanHostname.endsWith('.' + config.baseDomain)) {
        // Extrai o subdomínio
        const subdomain = cleanHostname.replace('.' + config.baseDomain, '');

        // landlord = rotas do landlord
        if (subdomain === 'landlord') {
            return landlordRoutes;
        }

        // Qualquer outro subdomínio = tenant
        return tenantRoutes;
    }

    // Fallback para site se não reconhecer o padrão
    return siteRoutes;
};

const routes: Array<any> = [
    {
        path: "/",
        name: "Home",
        component: () => import("../views/Home.vue"),
    },
    ...getGuardFromRoutes() || [],
    {
        path: "/:pathMatch(.*)",
        component: () => import("../views/404.vue"),
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach(async (to, _from, next) => {

    // Função para obter o contexto de uma rota (incluindo rotas pai)
    const getRouteContext = (route: any): string | null => {
        // Verifica meta da rota atual primeiro
        if (route.meta?.guard) {
            return route.meta.guard;
        }

        // Verifica meta das rotas pai (rotas aninhadas)
        for (const matched of route.matched) {
            if (matched.meta?.guard) {
                return matched.meta.guard;
            }
        }

        return null;
    };

    // Função para verificar se a rota requer autenticação
    const requiresAuth = (route: any): boolean => {
        return route.matched.some((record: any) => record.meta?.requiresAuth);
    };

    // Função para verificar se a rota requer usuário não autenticado
    const requiresGuest = (route: any): boolean => {
        return route.matched.some((record: any) => record.meta?.requiresGuest);
    };

    const context = getRouteContext(to);
    const { authenticated, attempt } = useAuth();
    // Para rotas que não requerem autenticação, permitir navegação
    if (!requiresAuth(to) && !requiresGuest(to)) {
        return next();
    }

    // Verificar autenticação com o backend se necessário
    let isAuthenticated = authenticated.value;

    if (!isAuthenticated && requiresAuth(to)) {
        const loginRoute = context === 'landlord' ? 'LandlordLogin' : context === 'tenant' ? 'TenantLogin' : 'Home';
        return next({ name: loginRoute });
    }

    // 1. Redirecionamento para dashboard se já autenticado e tentando acessar login
    if (requiresGuest(to) && isAuthenticated) {
        const dashboardRoute = context === 'landlord' ? 'LandlordDashboard' : context === 'tenant' ? 'TenantDashboard' : 'Home';
        return next({ name: dashboardRoute });
    }

    // 2. Redirecionamento para login se não autenticado e tentando acessar área protegida
    if (requiresAuth(to) && !isAuthenticated) {
        const loginRoute = context === 'landlord' ? 'LandlordLogin' : context === 'tenant' ? 'TenantLogin' : 'Home';
        return next({ name: loginRoute });
    }

    // 3. Permitir navegação
    next();
});

export default router