<template>
  <div v-if="!config.switch_legacy_view">
    <Home></Home>
  </div>
  <div v-else>
    <HomeLegacy></HomeLegacy>
  </div>
</template>

<script>
import Home from '@/views/Home.vue';
import HomeLegacy from '@/views/HomeLegacy.vue';
import { SettingsMixin } from '@/mixins/SettingsMixin';

export default {
  name: 'HomeWrapper',
  mixins: [SettingsMixin],
  components: {
    Home,
    HomeLegacy,
  },
};
</script>
