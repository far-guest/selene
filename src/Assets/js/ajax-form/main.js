import AjaxForm from './components/ajax-form'

export default {
    install(Vue, options) {
        Vue.component('ajax-form', AjaxForm);
    }
}