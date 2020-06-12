<template>
  <div class="login">
    <h2 class="title">Sign In</h2>
    <DummyAuth v-if="authPlugin === 'dummy'"/>
    <CustomAuth v-else-if="authPlugin === 'custom'"/>
    <PersonifyAuth v-else-if="authPlugin === 'personify'"/>
    <div v-else>
      No auth plugin was set.
    </div>
  </div>
</template>

<script>
import DummyAuth from '@/components/auth/DummyAuth.vue';
import CustomAuth from '@/components/auth/CustomAuth.vue';
import PersonifyAuth from '@/components/auth/PersonifyAuth.vue';

export default {
  name: 'Login',
  components: {
    DummyAuth,
    CustomAuth,
    PersonifyAuth,
  },
  data() {
    return {
    };
  },
  computed: {
    authPlugin() {
      return this.$store.getters.authPlugin;
    },
  },
  mounted() {
  },
  methods: {
    loginSuccess() {
      // if (this.destination) {
      // this.$router.push(this.destination);
      // this.$store.dispatch('unsetDestination');
      // } else {
      this.$router.push({ name: 'Home' });
      // }
    },
  },
};
</script>

<style lang="scss">
.login {
  color: $black;
}
</style>
