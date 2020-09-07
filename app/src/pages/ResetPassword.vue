<template>
 <v-ons-page>
  <v-ons-toolbar v-if="step != 3">
   <div class="left">
    <v-ons-back-button></v-ons-back-button>
   </div>
   <div class="center">
    Reset Password
   </div>
  </v-ons-toolbar>

  <div style="margin-top:20px; text-align:center">

   <transition name="fade" mode="out-in">
    <div key="step1" v-if="step == 1">

     <div class="field">
      <div class="label">
       <strong>Step 1 of 3</strong>
      </div>
     </div>

     <div class="field">
      <div class="label">
       Enter the email address<br>
       associated with your account<br>
       to receive a reset code.
      </div>
     </div>

     <div class="field">
      <div class="control">
       <v-ons-input
         key="email"
         v-model="email"
         float
         modifier="underbar"
         placeholder="Email"
         type="email">
       </v-ons-input>
      </div>
      <transition name="fade">
       <p v-if="errors.email" class="help error">{{errors.email}}</p>
      </transition>
     </div>

     <div class="field">
      <div class="input">
       <v-ons-button @click="onSendCode" :disabled="isSendCodeDisabled">
        Send Code
       </v-ons-button>
      </div>
     </div>
    </div>

    <div key="step2" v-else-if="step == 2">
     <div class="field">
      <div class="label">
       <strong>Step 2 of 3</strong>
      </div>
     </div>

     <div class="field">
      <div class="label">
       Enter the six-digit reset<br>
       code sent to your email.
      </div>
     </div>

     <div class="field">
      <div class="control">
       <v-ons-input
         v-model="code"
         float
         modifier="underbar"
         placeholder="Reset Code"
         type="text">
       </v-ons-input>
      </div>
      <transition name="fade">
       <p v-if="errors.code" class="help error">{{errors.code}}</p>
      </transition>
     </div>

     <div class="field" style="margin-bottom:40px">
      <div class="input">
       <v-ons-button @click="onVerifyCode" :disabled="isVerifyCodeDisabled">
        Verify Code
       </v-ons-button>
      </div>
     </div>

     <div class="field">
      <div class="label">
       Invalid or expired code?
      </div>
      <div class="control">
       <v-ons-button modifier="quiet" @click="tryAgain">
        Try Again
       </v-ons-button>
      </div>
     </div>
    </div>

    <div key="step3" v-else-if="step == 3">
     <div class="field">
      <div class="label">
       <strong>Step 3 of 3</strong>
      </div>
     </div>

     <div class="field">
      <div class="label">
       Enter your new password.<br>
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
      <p v-if="errors.password" class="help error">{{errors.password}}</p>
      <p v-else class="help">Minimum 8 characters</p>
     </div>

     <div class="field">
      <div class="input">
       <v-ons-button
         @click="onUpdatePassword"
         :disabled="isUpdatePasswordDisabled">
        Update Password
       </v-ons-button>
      </div>
     </div>
    </div>
   </transition>

  </div>
 </v-ons-page>
</template>

<script>
export default {
    name: 'ResetPassword',

    data() {
        return {
            step: 1,
            email: '',
            code: '',
            password: '',
            isSendCodeDisabled: false,
            isVerifyCodeDisabled: false,
            isUpdatePasswordDisabled: false,
            errors: {}
        };
    },

    mounted() {
        const email = localStorage.getItem('email');
        if (email) {
            this.step = 2;
        }
    },

    methods: {
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

                if (this.errors.email) {
                    this.$delete(this.errors, 'email');
                }

                resolve(true);
            });
        },

        onSendCode() {
            this.validateEmail().then(isValid => {
                if (!isValid) {
                    return;
                }

                this.isSendCodeDisabled = true;
                const email = this.email.trim();

                this.$apiService.passwordResetCode({
                    email: email
                })
                    .then(result => {
                        this.isSendCodeDisabled = false;

                        if (!result) {
                            this.$ons.notification.alert(
                                'A network or server error caused the ' +
                                'attempt to send a reset code to fail. ' +
                                'Check your network connection and try ' +
                                'again. If the problem persists, please ' +
                                'submit a support request.',
                                {title: 'Send Reset Code Failed'}
                            );
                            return;
                        }

                        // save email to localStorage so it's still available
                        // if the user/os closes the app
                        localStorage.setItem('email', email);

                        // ensure user always enters step 2 with a clean view
                        this.code = '';
                        this.$delete(this.errors, 'code');
                        this.step = 2;
                    });
            });
        },

        validateCode() {
            return new Promise(resolve => {
                const codeRegex = /^\d{6}$/;
                if (!this.code.trim().match(codeRegex)) {
                    this.$set(
                        this.errors,
                        'code',
                        'Invalid code'
                    );
                    resolve(false);
                    return;
                }

                if (this.errors.code) {
                    this.$delete(this.errors, 'code');
                }

                resolve(true);
            });
        },

        onVerifyCode() {
            this.validateCode().then(isValid => {
                if (!isValid) {
                    return;
                }

                this.isVerifyCodeDisabled = true;
                const email = localStorage.getItem('email');

                this.$apiService.signIn({
                    email: email,
                    code: this.code
                })
                    .then(result => {
                        this.isVerifyCodeDisabled = false;

                        if (!result) {
                            this.$ons.notification.alert(
                                'A network or server error caused the code ' +
                                'verification attempt to fail. Check your ' +
                                'network connection and try again. If the ' +
                                'problem persists, please submit a support ' +
                                'request.',
                                {title: 'Verification Failed'}
                            );
                            return;
                        }

                        if (result.status == 401) {
                            this.$set(
                                this.errors,
                                'code',
                                'Invalid code'
                            );
                            return;
                        }

                        this.$store.commit('auth/signIn', result.data.token);
                        localStorage.removeItem('email');

                        this.step = 3;
                    });
            });
        },

        tryAgain() {
            this.email = '';
            this.$delete(this.errors, 'email');

            this.step = 1;
            this.$delete(this.errors, 'code');
            localStorage.removeItem('email');
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

        onUpdatePassword() {
            this.validatePassword().then(isValid => {
                if (!isValid) {
                    return;
                }

                this.isUpdatePasswordDisabled = true;

                this.$apiService.updatePassword(this.password)
                    .then(result => {
                        this.isUpdatePasswordDisabled = false;

                        if (!result) {
                            this.$ons.notification.alert(
                                'A network or server error caused the ' +
                                'password reset attempt to fail. Check your ' +
                                'network connection and try again. If the ' +
                                'problem persists, please submit a support ' +
                                'request.',
                                {title: 'Password Reset Failed'}
                            );
                            return;
                        }

                        this.$emit('nav-reset', 'HomePage');
                        this.$ons.notification.toast({
                            message: 'Password reset successful',
                            buttonLabel: 'OK'
                        });
                    });
            });
        },
    }
};
</script>
