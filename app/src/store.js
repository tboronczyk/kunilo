import Vue from 'vue'
import Vuex from 'vuex'
import jwt_decode from 'jwt-decode';

Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'

const auth = {
    namespaced: true,

    state: {
        jwt: '',
        jwtDecoded : {}
    },

    getters: {
        jwt: state => state.jwt,
        username: state => state.jwtDecoded.username || ''
    },

    mutations: {
        signIn(state, jwt) {
            state.jwt = jwt;
            state.jwtDecoded = jwt_decode(jwt);
            localStorage.setItem('jwt', jwt);
        },

        signOut(state) {
            state.jwt = '';
            state.jwtDecoded = {};
            localStorage.removeItem('jwt');
        }
    }
}

export default new Vuex.Store({
    strict: debug,

    modules: {
        auth
    }
});
