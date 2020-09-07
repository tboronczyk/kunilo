import SignInPage from './pages/SignIn.vue';
import RegisterPage from './pages/Register.vue';
import HomePage from './pages/Home.vue';
import ResetPasswordPage from './pages/ResetPassword.vue';

const routes = {
    SignInPage,
    RegisterPage,
    HomePage,
    ResetPasswordPage
};

export default {
    data() {
        return {
            pageStack: []
        }
    },

    methods: {
        navReset(route) {
            this.pageStack = [routes[route]];
        },

        navPush(route) {
            this.pageStack.push(routes[route]);
        },

        navPop() {
            if (this.pageStack.length < 2) {
                console.log('length of pageStack restricted pop operation');
                return;
            }
            this.pageStack.pop();
        },

        navReplace(route) {
            this.pageStack[this.pageStack.length - 1] = routes[route];
        }
    }
}
