<template>
  <div class="plugin-custom personify-auth container">
    <div v-if="this.error" class="alert alert-danger">
      <span>{{ this.error }}</span>
    </div>
    <div v-else class="text-center">
      <p>You will be logged in with your Personify account, please wait...</p>
      <Spinner></Spinner>
    </div>
  </div>
</template>

<script>
import Spinner from '@/components/Spinner.vue';

export default {
  name: 'PersonifyAuth',
  components: {
    Spinner,
  },
  data() {
    return {
      error: '',
    };
  },
  async mounted() {
    await this.$store
      .dispatch('personifyAuthorize')
      .then(() => {
        const { appUrl } = this.$store.state.auth;
        if (appUrl !== undefined && appUrl.length > 0) {
          window.location = appUrl;
        } else {
          this.$router.push({ name: 'Home' }).catch(() => {});
        }
      })
      .catch((error) => {
        this.error = error.response ? error.response.data.message : 'Something went wrong!';
        throw error;
      });
  },
};
</script>

<style scoped>

</style>
