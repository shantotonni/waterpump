import axios from "axios";
const state = {
    user: {},
    token: localStorage.getItem('auth') || '',
    status: '',
};
const getters = {
    isAuthenticated: state => !!state.token,
    authStatus: state => state.status,
};
const actions = {
    loginUser( { commit }, user){
        axios
        .post("/api/auth/login", {
            staffid: user.staffid,
            password: user.password
        })
        .then( response => {
            const token = response.data.token
            localStorage.setItem('auth', token)
            commit('AUTH_SUCCESS', token)
        })
        .catch(err => {
            commit('AUTH_ERROR', err)
            localStorage.removeItem('user-token')
        })
    }
};
const mutations = {
    AUTH_SUCCESS(state, token) {
        state.token = token;
        state.status = 'success';
    },
    AUTH_ERROR(state) {
        state.status = 'error';
    }
}
export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
