<template>
  <span class="menu">
    <a :href="logoutUrl">Sign out</a>
  </span>
</template>

<script>
export default {
  name: 'LogoutLink',
  data() {
    return {
      logoutUrl: window.drupalSettings.openy_gated_content.logoutUrl,
    };
  },
};
</script>
