<template>
 <v-ons-page>
  <div style="text-align:center">
   <div style="margin-bottom:20px">
    <img src="img/logo.png" height="175" width="175">
   </div>

   <div class="field">
    <div class="control">
     <v-ons-input
       v-model="username"
       float
       modifier="underbar"
       placeholder="Username"
       @blur="validateUsername">
     </v-ons-input>
    </div>
    <transition name="fade">
     <p v-if="errors.username" class="help error">{{errors.username}}</p>
    </transition>
   </div>

   <div class="field">
    <div class="control">
     <v-ons-input
       v-model="password"
       float
       modifier="underbar"
       placeholder="Password"
       type="password"
       @blur="validatePassword">
     </v-ons-input>
    </div>
    <p v-if="errors.password" class="help error">{{errors.password}}</p>
    <p v-else class="help">Minimum 8 characters</p>
   </div>

   <div class="field">
    <div class="control">
     <v-ons-input
       v-model="email"
       float
       modifier="underbar"
       placeholder="Email"
       type="email"
       @blur="validateEmail">
     </v-ons-input>
    </div>
    <transition name="fade">
     <p v-if="errors.email" class="help error">{{errors.email}}</p>
    </transition>
   </div>

   <div class="field">
    <div class="control inline">
     <v-ons-checkbox
       v-model="confirm"
       input-id="confirm"
       style="margin-right:5px"
       @click="validateConfirm">
     </v-ons-checkbox>
    </div>
    <div class="label inline small">
     <label for="confirm">I have read and accept the</label><br/>
     <a href="">Terms and Conditions</a>.
    </div>
    <transition name="fade">
     <p v-if="errors.confirm" class="help error">{{errors.confirm}}</p>
    </transition>
   </div>

   <div class="field" style="margin-bottom:40px">
    <div class="input">
     <v-ons-button @click="register" :disabled="registerDisabled">
      Register
     </v-ons-button>
    </div>
   </div>

   <div class="field">
    <div class="label">
     Already have an account?
    </div>
    <div class="control">
     <v-ons-button modifier="quiet" @click="signIn">
      Sign In
     </v-ons-button>
    </div>
   </div>

  </div>
 </v-ons-page>
</template>

<script>
export default {
    name: 'Register',

    data() {
        return {
            username: '',
            password: '',
            email: '',
            confirm: false,
            registerDisabled: false,
            errors: {}
        };
    },

    methods: {
        validateUsername() {
            return new Promise(resolve => {
                const username = this.username.trim();
                if (username.length == 0) {
                    this.$set(this.errors, 'username', 'Enter a username');
                    resolve(false);
                    return;
                }

                this.$apiService.usernameAvailable(username)
                    .then(result => {
                        if (!result) {
                            this.$set(
                                this.errors,
                                'username',
                                'Username not available'
                            );
                            resolve(false);
                            return;
                        }

                        if (this.errors.username) {
                            this.$delete(this.errors, 'username');
                        }

                        resolve(true);
                   });
                });
        },

        validatePassword() {
            return new Promise(resolve => {
                if (this.password.length < 8) {
                    this.$set(
                        this.errors,
                        'password',
                        'Minimum 8 characters'
                    );
                    resolve(false);
                    return;
                }

                if (this.errors.password) {
                    this.$delete(this.errors, 'password');
                }

                resolve(true);
            });
        },

        validateEmail() {
            return new Promise(resolve => {
                const email = this.email.trim();
                const emailRegex = /^[^@]+@[^@.]+(\.[^@.]+)+$/;
                if (!email.match(emailRegex)) {
                    this.$set(
                        this.errors,
                        'email',
                        'Enter an email address'
                    );
                    resolve(false);
                    return;
                }

                this.$apiService.emailAvailable(email)
                    .then(result => {
                        if (!result) {
                            this.$set(
                                this.errors,
                                'email',
                                'Email already in use'
                            );
                            resolve(false);
                            return;
                        }

                        if (this.errors.email) {
                            this.$delete(this.errors, 'email');
                        }

                        resolve(true);
                   });
                });
        },

        validateConfirm() {
            return new Promise(resolve => {
                setTimeout(() => {
                    if (!this.confirm) {
                        this.$set(
                            this.errors,
                            'confirm',
                            'Read and accept terms'
                        );
                        resolve(false);
                        return;
                    }

                    if (this.errors.confirm) {
                        this.$delete(this.errors, 'confirm');
                    }

                    resolve(true);
                }, 10);
            });
        },

        validate() {
            return Promise.allSettled([
                this.validateUsername(),
                this.validatePassword(),
                this.validateEmail(),
                this.validateConfirm()
            ])
                .then(results => {
                    const errors = results.filter(result => !result.value);
                    return errors.length == 0;
                });
        },

        register() {
            this.registerDisabled = true;
            this.validate().then(isValid => {
                if (!isValid) {
                    this.registerDisabled = false;
                    return;
                }

                this.$apiService.register({
                    username: this.username,
                    password: this.password,
                    email: this.email
                })
                    .then(result => {
                        if (!result) {
                            this.$ons.notification.alert(
                                'A network or server error caused the ' +
                                'registration attempt to fail. Check your ' +
                                'network connection and try again. If the ' +
                                'problem persists, please submit a support ' +
                                'request.',
                                {title: 'Registration Failed'}
                            );
                            this.registerDisabled = false;
                            return;
                        }

                        this.$store.commit('auth/signIn', result.data.token);
                        this.$emit('nav-replace', 'HomePage');
                    });
            });
        },

        signIn() {
            this.$emit('nav-pop');
        }
    }
};
</script>
