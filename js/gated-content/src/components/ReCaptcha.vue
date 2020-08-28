<template>
  <div v-if="siteKey" class="recaptcha">
    <vue-recaptcha
      ref="vueRecaptcha"
      :sitekey="siteKey"
      @verify="verify($event)"
      @expired="expired"
    />
  </div>
</template>

<script>
import VueRecaptcha from 'vue-recaptcha';

export default {
  name: 'ReCaptcha',
  components: { VueRecaptcha },
  props: {
    reCaptchaKey: {
      type: String,
      default: '',
    },
    value: {
      type: String,
      default: '',
    },
  },
  computed: {
    siteKey() {
      return this.$props.reCaptchaKey;
    },
  },
  methods: {
    verify(response) {
      this.$emit('input', response);
    },
    expired() {
      this.$emit('input', '');
    },
    reset() {
      this.$refs.vueRecaptcha.reset();
      this.$emit('input', '');
    },
  },
};
</script>

<style lang="scss">
.recaptcha {
  padding: 5px 0;

  @include media-breakpoint-up('sm') {
    padding: 15px 0;
  }

  > div {
    display: flex;
    justify-content: space-around;
  }
}
</style>
