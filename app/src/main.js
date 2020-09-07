import Vue from 'vue';
import VueOnsen from 'vue-onsenui';
import apiService from './apiService';
import store from './store';
import App from './App.vue';

Vue.config.productionTip = false;

Vue.use(apiService);
Vue.use(VueOnsen);

const jwt = localStorage.getItem('jwt');
if (jwt) {
    store.commit('auth/signIn', jwt);
}

new Vue({
    store,
    render: h => h(App)
}).$mount('#app');
