<template>
  <div class="paragraph-headline" :style="styleObject">
    <div class="paragraph-headline__content">
      <div
        v-if="getHeadlineTitle"
        class="paragraph-headline__title"
      >{{ getHeadlineTitle }}
      </div>
      <div
        v-if="getHeadlineDescription"
        class="paragraph-headline__description"
        v-html="getHeadlineDescription"
      ></div>
      <a
        v-if="getHeadlineLinkText"
        class="paragraph-headline__link"
        :href="getHeadlineLinkUrl"
      >{{ getHeadlineLinkText }}</a>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  computed: {
    ...mapGetters([
      'getHeadlineTitle',
      'getHeadlineDescription',
      'getHeadlineLinkUrl',
      'getHeadlineLinkText',
      'getHeadlineBackgroundImage',
    ]),
    styleObject() {
      return {
        'background-image': this.getHeadlineBackgroundImage
          ? `url('${this.getHeadlineBackgroundImage}')`
          : '',
      };
    },
  },
};
</script>
