<template>
 <v-ons-page>
  <div style="text-align:center">

   <div style="margin-bottom:20px">
    <img src="img/logo.png" height="175" width="175">
   </div>

   <transition name="fade">
    <div v-if="isLoginInvalid" class="field">
     <div class="label error">
      Incorrect username and/or password.
     </div>
    </div>
   </transition>

   <div class="field">
    <div class="control">
     <v-ons-input
       v-model="username"
       float
       modifier="underbar"
       placeholder="Username">
     </v-ons-input>
    </div>
   </div>

   <div class="field">
    <div class="control">
     <v-ons-input
       v-model="password"
       float
       modifier="underbar"
       placeholder="Password"
       type="password">
     </v-ons-input>
    </div>
   </div>

   <div class="field" style="margin-bottom:40px">
    <div class="input">
     <v-ons-button @click="onSignIn" :disabled="isSignInDisabled">
      Sign In
     </v-ons-button>
    </div>
   </div>

   <div class="field">
    <div class="label">
     Don’t have an account?
    </div>
    <div class="control">
     <v-ons-button modifier="quiet" @click="onRegister">
      Register
     </v-ons-button>
    </div>
   </div>

   <div class="field">
    <div class="label">
     Forgot your password?
    </div>
    <div class="control">
     <v-ons-button modifier="quiet" @click="onResetPassword">
      Reset Password
     </v-ons-button>
    </div>
   </div>

  </div>
 </v-ons-page>
</template>

<script>
export default {
    name: 'SignIn',

    data() {
        return {
            username: '',
            password: '',
            isLoginInvalid: false,
            isSignInDisabled: false
        };
    },

    methods: {
        // the component is not destroyed on nav-push so this provides a way
        // to clear state so the user sees a clean page when they nav-pop
        clear() {
            this.username = '';
            this.password = '';
            this.isLoginInvalid = false;
            this.isSignInDisabled = false;
        },

        onSignIn() {
            this.isLoginInvalid = false;

            const username = this.username.trim();
            const password = this.password;

            if (username.length == 0 || password.length == 0) {
                this.isLoginInvalid = true;
                return;
            }

            this.isSignInDisabled = true;

            this.$apiService.signIn({
                username: username,
                password: password
            })
                .then(result => {
                    this.isSignInDisabled = false;

                    if (!result) {
                        this.$ons.notification.alert(
                            'A network or server error caused the sign in ' +
                            'attempt to fail. Check your network connection ' +
                            'and try again. If the problem persists, please ' +
                            'submit a support request.',
                            {title: 'Sign In Failed'}
                        );
                        return;
                    }

                    if (result.status == 401) {
                        this.isLoginInvalid = true;
                        return;
                    }

                    this.$store.commit('auth/signIn', result.data.token);
                    this.$emit('nav-replace', 'HomePage');
                });
        },

        onRegister() {
            this.$emit('nav-push', 'RegisterPage');
            this.clear();
        },

        onResetPassword() {
            this.$emit('nav-push', 'ResetPasswordPage');
            this.clear();
        }
    }
};
</script>
