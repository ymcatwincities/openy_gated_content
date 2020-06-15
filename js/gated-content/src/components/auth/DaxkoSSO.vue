<template>
    <div>
        <p>You will be redirected to Daxko website for authentication</p>
    </div>
</template>
<script>
  export default {
    name: 'DaxkoSSO',
    data() {
      return {
        'error': '',
      };
    },
    computed: {
      config() {
        return this.$store.getters.getDaxkoSSOConfig;
      },
    },
    async mounted() {
      await this.$store
        .dispatch('daxkossoAuthorize')
          .then(() => {
            this.$router.push({ name: 'Home' });
          })
        .catch((error) => {

          this.error = error.response ? error.response.data.message : 'Something went wrong!';
          throw error;
      });

    },
  };
</script>