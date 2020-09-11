<template>
  <div class="container">
    <div v-if="message" class="alert alert-info">
      <span v-html="message"></span>
      <a href="#" @click.prevent="resetForm">Back to Login form</a>
    </div>
    <div v-if="loading" class="spinner-center">
      <Spinner></Spinner>
    </div>
    <form v-show="!message && !loading" class="plugin-custom">
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
        <ReCaptcha ref="recaptcha" v-model="form.recaptchaToken" :reCaptchaKey="reCaptchaKey" />
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
    reCaptchaKey() {
      return this.$store.getters.getCustomReCaptchaKey;
    },
    config() {
      return this.$store.getters.getCustomConfig;
    },
  },
  methods: {
    resetForm() {
      this.error = '';
      this.message = '';
      this.form.email = '';
      this.loading = false;
      if (this.config.enableRecaptcha && this.$refs.recaptcha) {
        this.$refs.recaptcha.reset();
      }
    },
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
            this.loading = false;
            if (this.config.enableRecaptcha) {
              this.$refs.recaptcha.reset();
            }
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
          this.loading = false;
        });
    },
  },
};
</script>
