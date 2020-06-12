<template>
  <div class="plugin-custom personify-auth container">
    <div v-if="this.error" class="alert alert-danger">
      <span>{{ this.error }}</span>
    </div>
    <div v-else class="message">You will be logged in with your Personify account...</div>
  </div>
</template>

<script>
export default {
  name: 'PersonifyAuth',
  data() {
    return {
      error: '',
    };
  },
  async mounted() {
    await this.$store
      .dispatch('personifyAuthorize')
      .catch((error) => {
        this.error = error.response ? error.response.data.message : 'Something went wrong!';
        throw error;
      });

    this.$router.push({ name: 'Home' }).catch(() => {});
  },
};
</script>

<style scoped>

</style>
