<template>
  <div class="menu">
    <a @click.prevent="logout">sign out</a>
  </div>
</template>

<script>
export default {
  name: 'LogoutLink',
  methods: {
    logout() {
      this.$store.dispatch(`${this.$store.getters.authPlugin}Logout`);
      this.$router.push({ name: 'Login' });
    },
  },
};
</script>
