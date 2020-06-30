<template>
  <form class="plugin-custom">
    <div v-if="this.error" class="alert alert-danger">
      <span>{{ this.error }}</span>
    </div>
    <div class="form-group">
      <label for="auth-email">Email Address</label>
      <input
        v-model="form.email"
        placeholder="jondoe@example.com"
        type="email"
        id="auth-email"
        class="form-control"
        required
      >
    </div>
    <div v-if="config.enableRecaptcha">
      <ReCaptcha ref="recaptcha" v-model="form.recaptchaToken" />
    </div>
    <button @click.prevent="login" class="btn btn-lg btn-primary">Login</button>
  </form>
</template>

<script>
import ReCaptcha from '@/components/ReCaptcha.vue';

export default {
  name: 'CustomAuth',
  components: {
    ReCaptcha,
  },
  data() {
    return {
      loading: false,
      form: {
        email: '',
        recaptchaToken: '',
      },
      error: '',
    };
  },
  computed: {
    config() {
      return this.$store.getters.getCustomConfig;
    },
  },
  methods: {
    async login() {
      this.loading = true;
      this.error = '';
      await this.$store
        .dispatch('customAuthorize', this.form)
        .then(() => {
          const { appUrl } = this.$store.state.auth;
          if (appUrl !== undefined && appUrl.length > 0) {
            window.location = appUrl;
          } else {
            this.$router.push({ name: 'Home' }).catch(() => {});
          }
        })
        .catch((error) => {
          this.loading = false;
          this.error = error.response ? error.response.data.message : 'Something went wrong!';
          console.log(this.$refs);
          this.$refs.recaptcha.reset();
        });
    },
  },
};
</script>

<style scoped>

</style>
