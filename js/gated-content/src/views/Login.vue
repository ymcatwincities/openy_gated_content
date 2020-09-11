<template>
  <div class="login">
    <h2 class="title">Sign In</h2>
    <CustomAuth v-if="authPlugin === 'custom'"/>
    <DaxkoBarcode v-else-if="authPlugin === 'daxkobarcode'"/>
    <DaxkoSSO v-else-if="authPlugin === 'daxkosso'"/>
    <PersonifyAuth v-else-if="authPlugin === 'personify'"/>
    <div v-else>
      No auth plugin was set.
    </div>
  </div>
</template>

<script>
import CustomAuth from '@/components/auth/CustomAuth.vue';
import DaxkoBarcode from '@/components/auth/DaxkoBarcode.vue';
import DaxkoSSO from '@/components/auth/DaxkoSSO.vue';
import PersonifyAuth from '@/components/auth/PersonifyAuth.vue';

export default {
  name: 'Login',
  components: {
    DaxkoBarcode,
    DaxkoSSO,
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
  methods: {
    loginSuccess() {
      this.$router.push({ name: 'Home' });
    },
  },
};
</script>

<style lang="scss">
.login {
  color: $black;
}
</style>
