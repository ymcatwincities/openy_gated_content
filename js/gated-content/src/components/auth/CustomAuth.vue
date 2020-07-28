<template>
  <div class="container">
    <div v-if="message" class="alert alert-info">
      <span v-html="message"></span>
    </div>
    <div v-if="loading" class="spinner-center">
      <Spinner></Spinner>
    </div>
    <form v-else class="plugin-custom">
      <div v-if="error" class="alert alert-danger">
        <span>{{ error }}</span>
      </div>
      <div class="form-group">
        <label for="auth-email">Email Address</label>
        <input
          v-model="form.email"
          placeholder="johndoe@example.com"
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
  </div>
</template>

<script>
import ReCaptcha from '@/components/ReCaptcha.vue';
import Spinner from '@/components/Spinner.vue';

export default {
  name: 'CustomAuth',
  components: {
    ReCaptcha,
    Spinner,
  },
  data() {
    return {
      loading: false,
      form: {
        email: '',
        recaptchaToken: '',
        path: '',
      },
      error: '',
      message: '',
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
      const appUrl = this.$store.getters.getAppUrl;
      if (appUrl !== undefined && appUrl.length > 0) {
        this.form.path = appUrl;
      } else {
        this.form.path = window.location.pathname;
      }
      await this.$store
        .dispatch('customAuthorize', this.form)
        .then((response) => {
          if (response.status === 202) {
            this.message = response.data.message;
            this.form.email = '';
            if (this.config.enableRecaptcha) {
              this.$refs.recaptcha.reset();
            }
            this.loading = false;
            return;
          }
          if (appUrl !== undefined && appUrl.length > 0) {
            window.location = appUrl;
          } else {
            this.$router.push({ name: 'Home' }).catch(() => {});
          }
        })
        .catch((error) => {
          this.error = error.response ? error.response.data.message : 'Something went wrong!';
          if (this.config.enableRecaptcha) {
            this.$refs.recaptcha.reset();
          }
          this.loading = false;
        });
    },
  },
};
</script>
