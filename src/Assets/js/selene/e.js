window._ = require('lodash');
window.Popper = require('popper.js').default;

import Vue from 'vue'
import Vuex from 'vuex'
import VueRouter from 'vue-router'
import BootstrapVue from 'bootstrap-vue'
import VueSweetalert2 from 'vue-sweetalert2'
import VuePersianDatetimePicker from 'vue-persian-datetime-picker'

import AjaxForm from '../ajax-form/main'
import Selene from './main'

window.Vue = Vue;

Vue.use(Vuex);
Vue.use(VueRouter);
Vue.use(BootstrapVue);
Vue.use(VueSweetalert2);
Vue.use(VuePersianDatetimePicker, {name: 'date-picker', props: {}});
Vue.use(AjaxForm);
Vue.use(Selene);


Vue.mixin({
    methods: {
        route: route,
    }
});

try {
    window.$ = window.jQuery = require('jquery');
    require('bootstrap');
} catch (e) {
}

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.onCsrfToken = function (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': token}
    });
};

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    onCsrfToken(token.content)
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

const store = new Vuex.Store({
    state: {
        loading: false,
        loading_count: 0,
        user: window.user,
        resources: window.selene.resources,
        tools: window.selene.tools,
    },
    mutations: {
        loading(state, payload) {
            if (payload) {
                state.loading_count++;
                state.loading = true;
            } else {
                state.loading_count--;
                if (state.loading_count == 0) {
                    state.loading = false;
                }
            }
        },
    },
    actions: {
        error(context, error) {
            return new Promise((resolve, reject) => {
                Vue.swal({
                    type: 'error',
                    title: error.title,
                    text: error.text,
                }).then(x => resolve(x));
            })
        },
        si(context, {url, data, method}) {
            let config = {};

            null == method && (null == data ? method = "get" : method = "post");

            if (method == 'get') {
                data = {params: data}
            } else {
                for (let x in data) {
                    if (data.hasOwnProperty(x) && data[x] instanceof Blob) {
                        let formData = new FormData();
                        for (let y in data) {
                            if (data.hasOwnProperty(y)) {
                                if (data[y] instanceof Blob) {
                                    formData.append(y, data[y], data[x].name);
                                } else {
                                    let d = data[y] == null ? '' : JSON.stringify(data[y]);
                                    formData.append(y, d);
                                }
                            }
                        }
                        data = formData;
                        config.headers = {
                            'Content-Type': 'multipart/form-data'
                        };
                        break;
                    }
                }
            }

            context.commit('loading', true);

            return new Promise((resolve, reject) => {
                axios[method](url, data, config).then((result) => {
                    context.commit('loading', false);
                    resolve(result.data)
                }).catch((result) => {
                    context.commit('loading', false);

                    if (result.response) {
                        switch (result.response.status) {
                            case 401:
                                document.location.reload();
                                reject(result);
                                break;
                            case 422:
                                reject(result);
                                break;
                            case 500:
                                context.dispatch('error', {
                                    title: 'Internal Server Error',
                                    text: result.response.hasOwnProperty('data') && result.response.data.hasOwnProperty('message') ? result.response.data.message : result.message
                                }).then(x => reject(result));
                                break;
                            default:
                                context.dispatch('error', {
                                    title: 'Network Error ' + result.response.status,
                                    text: result.request.responseURL + ' ' + result.response.statusText
                                }).then(x => reject(result))
                        }
                    } else if (result.message) {
                        context.dispatch('error', {
                            title: 'Error Occurred',
                            text: result.message
                        }).then(x => reject(result))
                    }
                });
            });
        },
    }
});

const routes = [
    {
        name: 'resources',
        path: '/resources/:resource',
        component: require('./components/resource-index'),
        props: true
    },
    {
        name: 'resources.create',
        path: '/resources/:resource/create',
        component: require('./components/resource-form'),
        props: true
    },
    {
        name: 'resources.details',
        path: '/resources/:resource/:model/details',
        component: require('./components/resource-details'),
        props: true
    },
    {
        name: 'resources.type-action',
        path: '/resources/:resource/:model/:field/:action',
        component: require('./components/resource-type-action'),
        props: true
    },
    {
        name: 'resources.edit',
        path: '/resources/:resource/:model/edit',
        component: require('./components/resource-form'),
        props: true
    },
    {
        name: 'tools',
        path: '/tools/:name',
        component: require('./components/tool-container'),
        props: true
    },
];

const router = new VueRouter({
    routes,
    mode: 'history',
    base: 'selene/app',
    linkActiveClass: 'active'
});

window.vm = new Vue({
    el: '#app',
    store,
    router,
    data: {},
    computed: {
        isLoading() {
            return this.$store.state.loading
        },
        user() {
            return this.$store.state.user
        },
        resources() {
            return this.$store.state.resources
        },
        tools() {
            return this.$store.state.tools
        },
    },
    methods: {
        ...Vuex.mapMutations([
            'error',
            'loading',
        ]),
        ...Vuex.mapActions([
            'si',
        ]),
        logout() {
            this.si({url: this.route('logout'), method: 'post'}).then((data) => {
                window.location.reload()
            })
        }
    },
    mounted() {
        this.$nextTick(() => {
            $('#app').fadeIn()
        });
    }
});
