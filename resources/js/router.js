import Vue from 'vue';
import Vuex from 'vuex'
import Router from 'vue-router';

Vue.use(Router);
Vue.use(Vuex);

import Login from './components/login/LoginForm';
import FrontHome from './components/FrontHome';
import Home from './components/Home';
import EmployeeList from './components/employee/EmployeeList';
import TechnicianList from './components/technician/TechnicianList';
import DailySparePartsReport from './components/spares/SpareParts';
import ProvidedServiceReport from './components/report/ProvidedServiceReport';
import MergedServiceReport from './components/report/MergeServiceReport';
import ProvidedSelfServiceReport from './components/report/ProvidedSelfServiceReport';
import ProvidedOutsourceServiceReport from './components/report/ProvidedOutsourceServiceReport';
import ChangePassword from './components/settings/ChangePassword';
import SparePartsStock from './components/stock/Stock';
import ServiceDetailReport from './components/report/ServiceDetailReport';
import MonthlySparePartsReport from './components/spares/MonthlySparePartsReport';
import Brand from './components/brand/Brand';
import Product from './components/product/Product';
import EngineerWiseCSI from "./components/report/EngineerWiseCSI.vue";

const base_url = '/waterpump';

const routes = [
    {
        path: base_url,
        component:FrontHome,
        name:'FrontHome',
        redirect:{name : 'Login'}
    },
    {
        path: base_url+'/login',
        component:Login,
        name:'Login',
    },
    {
        path: base_url+'/home',
        component:Home,
        name:'Home',
    },
    {
        path: base_url+'/employee-list',
        component:EmployeeList,
        name:'Employee',
    },
    {
        path: base_url+'/technician-list',
        component: TechnicianList,
        name:'TechnicianList',
    },
    {
        path: base_url+'/daily-spare-parts-report',
        component:DailySparePartsReport,
        name:'DailySparePartsReport',
    },
    {
        path: base_url+'/monthly-spare-parts-report',
        component:MonthlySparePartsReport,
        name:'MonthlySparePartsReport',
    },
    {
        path: base_url+'/spare-parts-stock',
        component:SparePartsStock,
        name:'SparesStock',
    },
    {
        path: base_url+'/provided-service-report',
        component:ProvidedServiceReport,
        name:'Report',
    },
    {
        path: base_url+'/engineer-wise-csi-rating',
        component:EngineerWiseCSI,
        name:'EngineerWiseCSI',
    },
    {
        path: base_url+'/provided-merged-service-report',
        component:MergedServiceReport,
        name:'MergedReport',
    },
    {
        path: base_url+'/provided-self-service-report',
        component:ProvidedSelfServiceReport,
        name:'SelfReport',
    },
    {
        path: base_url+'/provided-outsource-service-report',
        component:ProvidedOutsourceServiceReport,
        name:'OutSourceReport',
    },
    {
        path: base_url+'/provided-service-detail-report',
        component:ServiceDetailReport,
        name:'DetailReport',
    },
    {
        path: base_url+'/change-password',
        component:ChangePassword,
        name:'ChangePassword',
    },
    {
        path: base_url+'/brand',
        component:Brand,
        name:'Brand',
    },
    {
        path: base_url+'/product',
        component:Product,
        name:'Product',
    },

];

export default new Router({
    linkExactActiveClass: "active",
    mode:'history',
    routes
});
