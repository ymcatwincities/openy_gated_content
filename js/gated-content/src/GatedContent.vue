<template>
  <div>
    <nav class="text-right gated-container" v-if="isLoggedIn">
      <router-link :to="{ name: 'Home' }">Home</router-link> |
      <LogoutLink />
    </nav>
    <router-view/>
  </div>
</template>

<script>
import LogoutLink from '@/components/LogoutLink.vue';
import { mapGetters } from 'vuex';

export default {
  name: 'GatedContent',
  props: ['auth', 'config', 'settings'],
  components: {
    LogoutLink,
  },
  computed: {
    ...mapGetters([
      'isLoggedIn',
    ]),
  },
  mounted() {
    this.$store.dispatch('setAuthPlugin', this.auth);
    this.$store.dispatch('setSettings', JSON.parse(this.settings));
    this.$store.dispatch(`${this.auth}Configure`, JSON.parse(this.config));
  },
};
</script>
