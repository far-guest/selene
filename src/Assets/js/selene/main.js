import ResourceIndex from './components/resource-index'
import ResourceDetails from './components/resource-details'
import ResourceForm from './components/resource-form'

import SeleneRelationSelector from './components/relation-selector'
import SeleneDataTable from './components/data-table'
import SeleneDataForm from './components/data-form'
import SeleneFieldFilterDialog from './components/field-filter-dialog'

import SeleneTypeText from './components/types/text'
import SeleneTypeSwitchText from './components/types/switch-text'
import SeleneTypePassword from './components/types/password'
import SeleneTypeNumber from './components/types/number'
import SeleneTypeEmail from './components/types/email'
import SeleneTypeTextarea from './components/types/textarea'
import SeleneTypeLiveTextarea from './components/types/live-textarea'
import SeleneTypeDatePicker from './components/types/date-picker'
import SeleneTypeBoolean from './components/types/boolean'
import SeleneTypeBelongsTo from './components/types/belongs_to'
import SeleneTypeBelongsToMany from './components/types/belongs_to_many'
import SeleneTypeBelongsToManyInline from './components/types/belongs_to_many_inline'
import SeleneTypeHasMany from './components/types/has_many'
import SeleneTypeHTML from './components/types/html'
import SeleneTypeFile from './components/types/file'
import SeleneTypeImage from './components/types/image'
import SeleneTypeRadio from './components/types/radio'
import SeleneTypeSelect from './components/types/select'
import SeleneTypeView from './components/types/view'
import SeleneTypeChart from './components/types/chart'

import SeleneMetric from './components/metrics/metric'
import SeleneMetricBar from './components/metrics/metric-bar'

import SelenePanelSimple from './components/panels/simple'
import SelenePanelTabbed from './components/panels/tabbed'

import SeleneMenu from './components/menu/selene-menu'
import SeleneAction from './components/action'

export default {
    install(Vue, options) {
        Vue.component('selene-resource-index', ResourceIndex);
        Vue.component('selene-resource-details', ResourceDetails);
        Vue.component('selene-resource-form', ResourceForm);

        Vue.component('selene-relation-selector', SeleneRelationSelector);
        Vue.component('selene-data-table', SeleneDataTable);
        Vue.component('selene-data-form', SeleneDataForm);
        Vue.component('selene-field-filter-dialog', SeleneFieldFilterDialog);

        Vue.component('selene-type-text', SeleneTypeText);
        Vue.component('selene-type-switch-text', SeleneTypeSwitchText);
        Vue.component('selene-type-password', SeleneTypePassword);
        Vue.component('selene-type-number', SeleneTypeNumber);
        Vue.component('selene-type-email', SeleneTypeEmail);
        Vue.component('selene-type-textarea', SeleneTypeTextarea);
        Vue.component('selene-type-live_textarea', SeleneTypeLiveTextarea);
        Vue.component('selene-type-date_picker', SeleneTypeDatePicker);
        Vue.component('selene-type-boolean', SeleneTypeBoolean);
        Vue.component('selene-type-belongs_to', SeleneTypeBelongsTo);
        Vue.component('selene-type-belongs_to_many', SeleneTypeBelongsToMany);
        Vue.component('selene-type-belongs_to_many_inline', SeleneTypeBelongsToManyInline);
        Vue.component('selene-type-has_many', SeleneTypeHasMany);
        Vue.component('selene-type-html', SeleneTypeHTML);
        Vue.component('selene-type-file', SeleneTypeFile);
        Vue.component('selene-type-image', SeleneTypeImage);
        Vue.component('selene-type-radio', SeleneTypeRadio);
        Vue.component('selene-type-select', SeleneTypeSelect);
        Vue.component('selene-type-view', SeleneTypeView);
        Vue.component('selene-type-chart', SeleneTypeChart);

        Vue.component('selene-metric', SeleneMetric);
        Vue.component('selene-metric-bar', SeleneMetricBar);

        Vue.component('selene-menu', SeleneMenu);
        Vue.component('selene-action', SeleneAction);

        Vue.component('selene-panel-simple', SelenePanelSimple);
        Vue.component('selene-panel-tabbed', SelenePanelTabbed);

        require('./components/tools/main');
    }
}
