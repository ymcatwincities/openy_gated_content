<template>
    <div>
        <div v-if="this.error" class="alert alert-danger">
            <span>{{ this.error }}</span>
        </div>
      <div v-if="loading" class="text-center">
        <p>We are registering you, using Daxko, please wait.</p>
        <Spinner></Spinner>
      </div>
    </div>
</template>
<script>
import Spinner from '@/components/Spinner.vue';

export default {
  name: 'DaxkoSSO',
  components: {
    Spinner,
  },
  data() {
    return {
      error: '',
      loading: true,
    };
  },
  async mounted() {
    await this.$store
      .dispatch('daxkossoAuthorize')
      .then(() => {
        const { appUrl } = this.$store.state.auth;
        if (appUrl !== undefined && appUrl.length > 0) {
          window.location = appUrl;
        } else {
          this.loading = false;
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
