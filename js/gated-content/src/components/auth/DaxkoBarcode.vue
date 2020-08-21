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
        <p>{{ error }}</p>
        <p v-if="help" v-html="help"></p>
      </div>
      <div class="form-group">
        <label for="auth-barcode">{{ config.formLabel || 'Barcode' }}</label>
        <input
          v-model="form.barcode"
          placeholder=""
          type="text"
          id="auth-barcode"
          class="form-control"
          required
        >
        <small v-if="config.formDescription" class="form-text">{{ config.formDescription }}</small>
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
  name: 'DaxkoBarcode',
  components: {
    ReCaptcha,
    Spinner,
  },
  data() {
    return {
      loading: false,
      form: {
        barcode: '',
        recaptchaToken: '',
      },
      error: '',
      message: '',
      help: '',
    };
  },
  computed: {
    config() {
      return this.$store.getters.getDaxkoBarcodeConfig;
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
        .dispatch('daxkobarcodeAuthorize', this.form)
        .then((response) => {
          if (response.status === 202) {
            this.message = response.data.message;
            this.help = response.data.help;
            this.form.barcode = '';
            this.loading = false;
            if (this.config.enableRecaptcha) {
              // @TODO: Uncommenting below throws an error. It seems like it should be there,
              // but doesn't seem to break anything by being off.
              // this.$refs.recaptcha.reset();
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
          this.help = error.response ? error.response.data.help : '';
          this.loading = false;
          if (this.config.enableRecaptcha) {
            // @TODO: see above.
            // this.$refs.recaptcha.reset();
          }
        });
    },
  },
};
</script>
