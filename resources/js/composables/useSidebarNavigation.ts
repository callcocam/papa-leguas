import { computed, ref, watch, onMounted } from "vue";
import { useRoute, type RouteLocationNamedRaw } from "vue-router";
import { useGenerateRoute } from "./useGenerateRoute";
import type { MenuItem } from "../types/menu";

const getDefaultRouteName = (): string => {
  const hostname = window.location.hostname;
  const baseDomain = (window as any).Laravel?.baseDomain || '';
  const cleanHostname = hostname.replace(/^www\./, '');

  if (cleanHostname.endsWith('.' + baseDomain)) {
    const subdomain = cleanHostname.replace('.' + baseDomain, '');
    if (subdomain === 'landlord') {
      return 'LandlordDashboard';
    }
    return 'TenantDashboard';
  }

  return 'LandlordDashboard';
};

export const useSidebarNavigation = () => {
  const route = useRoute();
  const { getIndexRoute, routeExists, getRoutesByResource } = useGenerateRoute();

  const openSubmenus = ref<string[]>([]);

  const loadMenuItems = (): MenuItem[] => {
    const menus = (window as any).Laravel?.menus || [];
    return menus;
  };

  const menuItems = computed<MenuItem[]>(() => loadMenuItems());

  const createMenuActiveChecker = (currentRouteName: string | null | undefined) => {
    const activeCache = new Map<string, boolean>();

    return (item: MenuItem): boolean => {
      if (!currentRouteName) return false;

      const cacheKey = `${item.id}-${currentRouteName}`;
      const cached = activeCache.get(cacheKey);
      if (cached !== undefined) return cached;

      const hasDirectMatch = item.route && currentRouteName === item.route;
      if (hasDirectMatch) {
        activeCache.set(cacheKey, true);
        return true;
      }

      const resourceRoutes = getRoutesByResource(item.id);
      const hasResourceMatch = resourceRoutes.some((r) => r.name === currentRouteName);
      if (hasResourceMatch) {
        activeCache.set(cacheKey, true);
        return true;
      }

      const hasChildRouteMatch = typeof currentRouteName === "string" &&
        currentRouteName.startsWith(`${item.id}.`);

      activeCache.set(cacheKey, hasChildRouteMatch);
      return hasChildRouteMatch;
    };
  };

  const isMenuActive = computed(() => {
    const checker = createMenuActiveChecker(route.name);
    return (item: MenuItem) => checker(item);
  });

  const createGroupActiveChecker = (menuActiveChecker: (item: MenuItem) => boolean) => {
    return (item: MenuItem): boolean => {
      if (!item.children || item.children.length === 0) return false;
      return item.children.some((child) => menuActiveChecker(child));
    };
  };

  const isGroupActive = computed(() => {
    const menuChecker = isMenuActive.value;
    return createGroupActiveChecker(menuChecker);
  });

  const openActiveGroups = () => {
    const groupChecker = isGroupActive.value;

    menuItems.value.forEach((item) => {
      const isGroupItem = item.type === "group" && item.children && item.children.length > 0;
      const shouldOpen = isGroupItem && groupChecker(item) && !openSubmenus.value.includes(item.id);

      if (shouldOpen) {
        openSubmenus.value.push(item.id);
      }
    });
  };

  const toggleSubmenu = (itemId: string) => {
    const index = openSubmenus.value.indexOf(itemId);
    if (index > -1) {
      openSubmenus.value.splice(index, 1);
    } else {
      openSubmenus.value.push(itemId);
    }
  };

  const isSubmenuOpen = (itemId: string): boolean => {
    return openSubmenus.value.includes(itemId);
  };

  const buildRouteLocation = (item: MenuItem): RouteLocationNamedRaw => {
    if (item.route && routeExists(item.route)) {
      return { name: item.route };
    }

    const indexRoute = getIndexRoute(item.id);
    const routeName = indexRoute?.name || `${item.id}.index`;

    return { name: routeName || getDefaultRouteName() };
  };

  watch(
    () => route.name,
    () => {
      openActiveGroups();
    },
    { immediate: true }
  );

  onMounted(() => {
    openActiveGroups();
  });

  return {
    menuItems,
    openSubmenus,
    isMenuActive,
    isGroupActive,
    toggleSubmenu,
    isSubmenuOpen,
    buildRouteLocation,
    openActiveGroups,
  };
};
