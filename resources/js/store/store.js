import Vuex from 'vuex';
import Vue from 'vue';

Vue.use(Vuex);

export const store = new Vuex.Store({
    state: {
        token: localStorage.getItem('auth') || '',
        userid: sessionStorage.getItem('userid') || '',
        usertype: sessionStorage.getItem('usertype') || '',
    },
    mutations: {
        setToken (state, token) {
            localStorage.setItem('auth', token);
            sessionStorage.setItem("authenticated", true);
            state.token = token;
        },
        clearToken (state) {
            localStorage.removeItem('auth');
            sessionStorage.removeItem("authenticated");
            state.token = '';
        },
        setUserId (state, userid) {
            sessionStorage.setItem("userid", userid);
            state.userid = userid;
        },
        clearUserId (state) {
            sessionStorage.removeItem('userid');
            state.userid = '';
        },
        setUserType (state, usertype) {
            sessionStorage.setItem("usertype", usertype);
            state.usertype = usertype;
        },
        clearUserType (state) {
            sessionStorage.removeItem('usertype');
            state.usertype = '';
        },
    }
})
