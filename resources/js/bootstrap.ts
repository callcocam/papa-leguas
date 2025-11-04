import axios from 'axios';

declare global {
    interface Window {
        axios: typeof axios;
        Laravel: {
            csrfToken: string;
            baseDomain: string;
            localDomains: string[];
            menus?: any;
        };
    }
}

window.axios = axios;

// Headers essenciais para SPA
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.headers.common['Content-Type'] = 'application/json';
window.axios.defaults.baseURL = window.location.origin;
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;
