<template>
 <v-ons-page>
  <div style="text-align:center">
   <h1>HOME</h1>
  </div>
 </v-ons-page>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'Home',

    computed: {
        ...mapGetters('auth', ['username'])
    }
}
</script>
