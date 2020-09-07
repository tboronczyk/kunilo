import Vue from 'vue';
import config from './config';
import store from './store';

const postOpts = {
    method: 'post',
    serializer: 'json',
    responseType: 'json'
};

const ApiService = {
    usernameAvailable(username) {
        return new Promise(resolve => {
            cordova.plugin.http.sendRequest(
                config.apiBaseUrl + '/account/user/' + username,
                { method: 'head' },
                (resp) => {
                    resolve(false);
                },
                (err) => {
                    if (err.status != 404) {
                        console.log(err);
                        resolve(false);
                        return;
                    }
                    resolve(true);
                }
            );
        });
    },

    emailAvailable(email) {
        return new Promise(resolve => {
            cordova.plugin.http.sendRequest(
                config.apiBaseUrl + '/account/email/' +
                    encodeURIComponent(email),
                { method: 'head' },
                (resp) => {
                    resolve(false);
                },
                (err) => {
                    if (err.status != 404) {
                        console.log(err);
                        resolve(false);
                        return;
                    }
                    resolve(true);
                }
            );
        });
    },

    register(userDetails) {
        return new Promise(resolve => {
            cordova.plugin.http.sendRequest(
                config.apiBaseUrl + '/user',
                {
                    ...postOpts,
                    data: userDetails
                },
                (resp) => {
                    resolve(resp);
                },
                (err) => {
                    console.log(err);
                    resolve(false);
                }
            );
        });
    },

    signIn(credentials) {
        return new Promise(resolve => {
            cordova.plugin.http.sendRequest(
                config.apiBaseUrl + '/account/auth',
                {
                    ...postOpts,
                    data: credentials
                },
                (resp) => {
                    resolve(resp);
                },
                (err) => {
                    if (err.status == 401 || err.status == 404) {
                        resolve(err);
                        return;
                    }
                    console.log(err);
                    resolve(false);
                }
            );
        });
    },

    passwordResetCode(userDetails) {
        return new Promise(resolve => {
            cordova.plugin.http.sendRequest(
                config.apiBaseUrl + '/account/password',
                {
                    ...postOpts,
                    data: userDetails
                },
                (resp) => {
                    resolve(resp);
                },
                (err) => {
                    console.log(err);
                    resolve(false);
                }
            );
        });
    },

    updatePassword(password) {
        return new Promise(resolve => {
            const token = store.getters['auth/jwt'];

            cordova.plugin.http.sendRequest(
                config.apiBaseUrl + '/user/password',
                {
                    ...postOpts,
                    data: {
                        token: token,
                        password: password
                    }
                },
                (resp) => {
                    resolve(resp);
                },
                (err) => {
                    console.log(err);
                    resolve(false);
                }
            );
        });
    }
};

export default {
    install(vue, options) {
        vue.prototype.$apiService = ApiService;
    }
};
