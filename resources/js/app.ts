import './bootstrap';
import { createApp, defineAsyncComponent } from 'vue'
import App from './App.vue'
import useAuth from './composables/useAuth';
import router from './router'
import ComponentRegistry from './utils/ComponentRegistry'
import BreadcrumbRegistry from './utils/BreadcrumbRegistry'

const { attempt } = useAuth()

/**
 * Auto-registro de componentes padrão do InfoList
 *
 * Estes componentes são registrados automaticamente e podem ser
 * sobrescritos pela aplicação se necessário.
 */
ComponentRegistry.registerBulk({
  'info-column-text': defineAsyncComponent(() => import('./components/infolist/columns/InfolistText.vue')),
  'info-column-email': defineAsyncComponent(() => import('./components/infolist/columns/InfolistEmail.vue')),
  'info-column-date': defineAsyncComponent(() => import('./components/infolist/columns/InfolistDate.vue')),
  'info-column-phone': defineAsyncComponent(() => import('./components/infolist/columns/InfolistPhone.vue')),
  'info-column-status': defineAsyncComponent(() => import('./components/infolist/columns/InfolistStatus.vue')),
  'info-column-boolean': defineAsyncComponent(() => import('./components/infolist/columns/InfolistBoolean.vue')),
  'info-column-card': defineAsyncComponent(() => import('./components/infolist/columns/InfolistCard.vue')),
})

/**
 * Auto-registro de componentes de formulário
 *
 * Estes componentes são usados em formulários e modais de ações
 */
ComponentRegistry.registerBulk({
  'form-column-text': defineAsyncComponent(() => import('./components/form/columns/FormColumnText.vue')),
  'form-column-textarea': defineAsyncComponent(() => import('./components/form/columns/FormColumnTextarea.vue')),
  'form-column-select': defineAsyncComponent(() => import('./components/form/columns/FormColumnSelect.vue')),
  'form-column-checkbox': defineAsyncComponent(() => import('./components/form/columns/FormColumnCheckbox.vue')),
  'form-column-date': defineAsyncComponent(() => import('./components/form/columns/FormColumnDate.vue')),
  'form-column-number': defineAsyncComponent(() => import('./components/form/columns/FormColumnNumber.vue')),
  'form-column-email': defineAsyncComponent(() => import('./components/form/columns/FormColumnEmail.vue')),
  'form-column-password': defineAsyncComponent(() => import('./components/form/columns/FormColumnPassword.vue')),
  'form-column-hidden': defineAsyncComponent(() => import('./components/form/columns/FormColumnHidden.vue')),
  'form-column-file-upload': defineAsyncComponent(() => import('./components/form/columns/FormColumnFileUpload.vue')),
})

ComponentRegistry.markAsInitialized()

/**
 * Auto-registro de componentes padrão do Breadcrumb
 *
 * Estes componentes são registrados automaticamente e podem ser
 * sobrescritos pela aplicação se necessário.
 */
BreadcrumbRegistry.registerBulk({
  'breadcrumb-default': defineAsyncComponent(() => import('./components/breadcrumbs/DefaultBreadcrumb.vue')),
})

BreadcrumbRegistry.markAsInitialized()

/**
 * Auto-registro de componentes padrão da Table
 *
 * Estes componentes são registrados automaticamente e podem ser
 * sobrescritos pela aplicação se necessário.
 */
import TableRegistry from './utils/TableRegistry'

TableRegistry.registerBulk({
  'table-default': defineAsyncComponent(() => import('./components/table/DefaultTable.vue')),
})

TableRegistry.markAsInitialized()

/**
 * Auto-registro de componentes padrão de Actions
 *
 * Estes componentes são registrados automaticamente e podem ser
 * sobrescritos pela aplicação se necessário.
 */
import ActionRegistry from './utils/ActionRegistry'

ActionRegistry.registerBulk({
  'action-button': defineAsyncComponent(() => import('./components/actions/types/ActionButton.vue')),
  'action-link': defineAsyncComponent(() => import('./components/actions/types/ActionLink.vue')),
  'action-link-confirm': defineAsyncComponent(() => import('./components/actions/types/ActionLinkConfirm.vue')),
  'action-a-link': defineAsyncComponent(() => import('./components/actions/types/ActionALink.vue')),
  'action-dropdown': defineAsyncComponent(() => import('./components/actions/types/ActionDropdown.vue')),
  'action-confirm': defineAsyncComponent(() => import('./components/actions/types/ActionConfirm.vue')),
  'action-modal': defineAsyncComponent(() => import('./components/actions/types/ActionModalForm.vue')),
  'action-modal-form': defineAsyncComponent(() => import('./components/actions/types/ActionModalForm.vue')), // Novo nome
  'LinkButton': defineAsyncComponent(() => import('./components/actions/types/ActionButton.vue')), // Alias para compatibilidade com backend
})

ActionRegistry.markAsInitialized()

/**
 * Auto-registro de componentes padrão de Filters
 *
 * Estes componentes são registrados automaticamente e podem ser
 * sobrescritos pela aplicação se necessário.
 */
import FilterRegistry from './utils/FilterRegistry'

FilterRegistry.registerBulk({
  'filter-text': defineAsyncComponent(() => import('./components/filters/types/FilterText.vue')),
  'filter-select': defineAsyncComponent(() => import('./components/filters/types/FilterSelect.vue')),
  'filter-multi-select': defineAsyncComponent(() => import('./components/filters/types/FilterMultiSelect.vue')),
  'filter-date': defineAsyncComponent(() => import('./components/filters/types/FilterDate.vue')),
  'filter-date-range': defineAsyncComponent(() => import('./components/filters/types/FilterDateRange.vue')),
  'filter-trashed': defineAsyncComponent(() => import('./components/filters/types/FilterTrashed.vue')),
})

FilterRegistry.markAsInitialized()

const app = createApp(App)

attempt().then(() => {
    app.use(router)
    app.mount('#app')
})