<template>
 <v-ons-splitter>
  <v-ons-splitter-side
    :open.sync="showMenu"
    side="right"
    width="220px"
    collapse>
   <v-ons-page>
    <v-ons-list>
     <v-ons-list-item tappable @click="signOut">
      Sign Out
     </v-ons-list-item>
    </v-ons-list>
   </v-ons-page>
  </v-ons-splitter-side>
  <v-ons-splitter-content>
   <v-ons-page>
    <v-ons-toolbar :visible="username.length > 0">
     <div class="left">
     </div>
     <div class="center">
     </div>
     <div class="right">
      <v-ons-toolbar-button @click="toggleShowMenu">
       <v-ons-icon icon="ion-ios-menu, material:md-more-vert"></v-ons-icon>
      </v-ons-toolbar-button>
     </div>
    </v-ons-toolbar>
    <v-ons-navigator
      :page-stack="pageStack"
      @nav-push="navPush"
      @nav-pop="navPop"
      @nav-replace="navReplace"
      @nav-reset="navReset">
    </v-ons-navigator>
   </v-ons-page>
  </v-ons-splitter-content>
 </v-ons-splitter>
</template>

<script>
import { mapGetters } from 'vuex';
import Navigator from './navigator';

export default {
    name: 'app',

    mixins: [Navigator],

    data() {
        return {
            showMenu: false
        }
    },

    computed: {
        ...mapGetters('auth', ['username'])
    },

    created() {
        if (this.username.length) {
            this.navReset('HomePage');
        } else {
            this.navReset('SignInPage');
        }
    },

    methods: {
        toggleShowMenu() {
            this.showMenu = !this.showMenu;
        },


        signOut() {
            this.showMenu = false;
            this.$store.commit('auth/signOut');
            this.navReset('SignInPage');
        }
    }
}
</script>
