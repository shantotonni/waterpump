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
    loginUser( {}, user){
        axios
        .post("/api/auth/login", {
            staffid: user.staffid,
            password: user.password
        })
        then( response => {
            const token = resp.data.token
            localStorage.setItem('auth', token)
            commit(AUTH_SUCCESS, token)
            console.log(response.data);
        })
        .catch(err => {
            commit(AUTH_ERROR, err)
            localStorage.removeItem('user-token')
            reject(err)
        })
    }
};
const mutations = {}
export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
