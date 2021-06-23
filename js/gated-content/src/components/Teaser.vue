<template>
  <div class="teaser">
    <router-link :to="{ name: routeName, params: { id: id } }">
      <div v-if="showImage"
           class="preview"
           :style="{backgroundImage: `url(${image})`}"
      >
        <slot name="overlay" />
      </div>
      <div class="info">
        <slot />
      </div>
    </router-link>
    <slot name="outer" />
  </div>
</template>

<script>
import { SettingsMixin } from '@/mixins/SettingsMixin';

export default {
  name: 'Teaser',
  mixins: [SettingsMixin],
  props: {
    routeName: {
      type: String,
      required: true,
    },
    id: {
      type: String,
      required: true,
    },
    component: {
      type: String,
    },
    image: {
      type: String,
      default: null,
    },
  },
  computed: {
    showImage() {
      return this.image && (!this.component || this.config.components[this.component].show_covers);
    },
  },
};
</script>
