<template>
  <div class="container">
    <div class="row">
      <div class="col-12 col-md-10 col-lg-5 my-5">
        <form>
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
          <button @click.prevent="login" class="btn btn-primary">Login!</button>
        </form>
      </div>
    </div>
  </div>
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
          this.$router.push({ name: 'Home' });
        })
        .catch((error) => {
          this.loading = false;
          // this.$refs.recaptcha.reset();
          // this.$refs.recaptchaValidation.reset();
          this.error = error.response ? error.response.data.message : 'Something went wrong!';
        });
    },
  },
};
</script>

<style scoped>

</style>
