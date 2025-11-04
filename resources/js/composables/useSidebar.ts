import { ref } from "vue";

// Estado global compartilhado entre todos os componentes
const isOpen = ref(true);  // Desktop sidebar começa aberta
const isMobileOpen = ref(false);  // Mobile fechada

export const useSidebar = () => {
    const toggleSidebar = () => {
        isOpen.value = !isOpen.value;
    };


    const toggleMobileSidebar = () => {
        isMobileOpen.value = !isMobileOpen.value;
    };

    return {
        isOpen,
        isMobileOpen,
        toggleSidebar,
        toggleMobileSidebar
    };
};