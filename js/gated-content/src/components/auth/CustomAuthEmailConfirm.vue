<template>
  <div class="container">
    <div v-if="error" class="error-wrapper">
      <div class="alert alert-danger">
        <span>{{ error }}</span>
      </div>

      <p class="text-center">
        <router-link :to="{ name: 'Login' }" class="btn btn-lg btn-primary">Sign In</router-link>
      </p>
    </div>
    <div v-if="loading" class="spinner-center">
      <Spinner></Spinner>
    </div>
  </div>
</template>

<script>
import Spinner from '@/components/Spinner.vue';

export default {
  name: 'CustomAuthEmailConfirm',
  components: {
    Spinner,
  },
  props: {
    id: {
      type: String,
      required: true,
    },
    token: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      loading: true,
      error: '',
    };
  },
  computed: {
    config() {
      return this.$store.getters.getCustomConfig;
    },
  },
  mounted() {
    this.confirmEmail();
  },
  methods: {
    async confirmEmail() {
      this.loading = true;
      await this.$store
        .dispatch('customEmailConfirmation', {
          id: this.id,
          token: this.token,
        })
        .then(() => {
          this.$router.push({ name: 'Home' }).catch(() => {});
        })
        .catch((error) => {
          this.error = error.response ? error.response.data.message : 'Something went wrong!';
          this.loading = false;
        });
    },
  },
};
</script>
