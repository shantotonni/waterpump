require('./bootstrap');
import Vue from 'vue';
import VueToastr2 from 'vue-toastr-2'
import 'vue-toastr-2/dist/vue-toastr-2.min.css'
window.toastr = require('toastr');
Vue.use(VueToastr2)

const moment = require('vue-moment');
require('moment/locale/es');
Vue.use(require('vue-moment'),{
    moment
})

import VModal from 'vue-js-modal'
Vue.use(VModal)

export const bus = new Vue();

const options = {
    name: '_blank',
    specs: [
      'fullscreen=yes',
      'titlebar=yes',
      'scrollbars=yes'
    ],
    styles: [
      'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css',
    ]
  }

import VueHtmlToPaper from 'vue-html-to-paper';
Vue.use(VueHtmlToPaper, options);

import excel from 'vue-excel-export'
Vue.use(excel)

import VueApexCharts from 'vue-apexcharts';
Vue.use(VueApexCharts);
Vue.component('apexchart', VueApexCharts);

import router from "./router.js";
import { store } from "./store/store.js";

// Auto-logout on 401 Unauthenticated response
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            store.commit('clearToken');
            store.commit('clearUserId');
            store.commit('clearUserType');
            if (router.currentRoute.name !== 'Login') {
                router.push({ name: 'Login' });
                window.toastr.error('Session expired. Please login again.');
            }
        }
        return Promise.reject(error);
    }
);

import { Form, HasError, AlertError } from 'vform'
window.Form = Form;
Vue.component(HasError.name, HasError)
Vue.component(AlertError.name, AlertError)

Vue.component('main-app', require("./MainApp.vue").default);

const app = new Vue({
    el: '#app',
    router,
    store: store,
});
