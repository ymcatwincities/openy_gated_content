<template>
  <div class="plugin-custom personify-auth container">
    <template v-if="error">
      <h3>You were not able to login</h3>
      <div class="alert alert-danger">
        <span>{{ this.error }}</span>
      </div>
      <a @click.prevent="tryAgain" class="btn btn-lg btn-primary">Try again</a>
    </template>
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
  mounted() {
    this.runLoginProcess();
  },
  methods: {
    async runLoginProcess() {
      await this.$store
        .dispatch('personifyAuthorize')
        .then(() => {
          this.$router.push({ name: 'Home' }).catch(() => {});
        })
        .catch((error) => {
          this.error = error.response ? error.response.data.message : 'Something went wrong!';
          throw error;
        });
    },
    tryAgain() {
      this.error = '';
      this.runLoginProcess();
    },
  },
};
</script>

<style scoped>

</style>
