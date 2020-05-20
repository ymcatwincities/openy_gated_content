<template>
  <div class="login">
    <div class="text-center my-4">
      <router-link :to="{ name: 'Home' }">Home</router-link>
    </div>
    <div class="plugin">
      {{ authPlugin }}
    </div>
    <DummyAuth v-if="authPlugin === 'dummy'"/>
    <DummyAuth v-else-if="authPlugin === 'dummy2'"/>
    <div v-else>
      No auth plugin was set.
    </div>
  </div>
</template>

<script>
import DummyAuth from '@/components/auth/DummyAuth.vue';

export default {
  name: 'Login',
  components: {
    DummyAuth,
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
