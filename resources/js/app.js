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

// import VModal from 'vue-js-modal'
// Vue.use(VModal)

export const bus = new Vue();

// import { jsPDF } from "jspdf";

const options = {
    name: '_blank',
    specs: [
      'fullscreen=yes',
      'titlebar=yes',
      'scrollbars=yes'
    ],
    styles: [
      'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css',
      './public/css/style.css'
    ]
  }

import VueHtmlToPaper from 'vue-html-to-paper';
Vue.use(VueHtmlToPaper, options);

import excel from 'vue-excel-export'
Vue.use(excel)

import VueApexCharts from 'vue-apexcharts';
Vue.use(VueApexCharts);
Vue.component('apexchart', VueApexCharts);

//import router
import router from "./router.js";
import { store } from "./store/store.js";

//import v-form
import { Form, HasError, AlertError } from 'vform'
window.Form = Form;
Vue.component(HasError.name, HasError)
Vue.component(AlertError.name, AlertError)

Vue.component('main-app', require("./MainApp.vue").default);
//Vue.component('pagination', require('./components/partial/PaginationComponent').default);

const app = new Vue({
    el: '#app',
    router,
    store: store,
});
