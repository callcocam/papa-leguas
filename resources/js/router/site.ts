

export const siteRoutes: Array<any> = [
    {
        path: "/",
        component: () => import('../views/Home.vue'),
        name: 'Home',
        meta: { requiresAuth: false }
    },
    {
        path: '/about',
        component: () => import('../views/About.vue'),
        name: 'About',
        meta: { requiresAuth: false }
    },
    {
        path: '/privacy-policy',
        component: () => import('../views/Policy.vue'),
        name: 'PrivacyPolicy',
        meta: { requiresAuth: false }
    },
    {
        path: '/terms-and-conditions',
        component: () => import('../views/Terms.vue'),
        name: 'Terms',
        meta: { requiresAuth: false }
    },
    {
        path: '/contact',
        component: () => import('../views/Contact.vue'),
        name: 'Contact',
        meta: { requiresAuth: false }
    },
    {
        path: '/pricing',
        component: () => import('../views/Pricing.vue'),
        name: 'Pricing',
        meta: { requiresAuth: false }
    },
];

export default siteRoutes;