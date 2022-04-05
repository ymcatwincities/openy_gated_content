<template>
  <div class="gated-content-categories-page">
    <div class="gated-containerV2 my-40-20 px--20-10 title-wrapper">
      <div class="title cachet-book-32-28 text-gray">{{ title }}</div>
    </div>

    <DurationsListing
      :title="'none'"
    />
  </div>
</template>

<script>
import { SettingsMixin } from '@/mixins/SettingsMixin';
import { FilterAndSortMixin } from '@/mixins/FilterAndSortMixin';
import DurationsListing from '@/components/duration/DurationsListing.vue';

export default {
  name: 'DurationsListingPage',
  mixins: [SettingsMixin, FilterAndSortMixin],
  components: {
    DurationsListing,
  },
  data() {
    return {
      DEFAULT_SORT: 'date_asc',
      filterOptions: [
        { value: 'title_asc', label: 'By title (A-Z)' },
        { value: 'title_desc', label: 'By title (Z-A)' },
      ],
    };
  },
  computed: {
    title() {
      if (this.selectedComponent === 'all' || typeof this.config.components[this.selectedComponent] === 'undefined') {
        return 'Duration';
      }

      return `${this.config.components[this.selectedComponent].title} Duration`;
    },
  },
};
</script>
