import { SettingsMixin } from '@/mixins/SettingsMixin';

export const ListingMixin = {
  mixins: [SettingsMixin],
  data() {
    return {
      component: null,
      listing: [],
    };
  },
  computed: {
    listingIsNotEmpty() {
      return this.listing.length > 0;
    },
    layoutClass() {
      if (this.component && this.config.components[this.component].show_covers) {
        return 'four-columns';
      }

      switch (this.listing.length) {
        case 1: return 'one-columns';
        case 2: return 'two-columns';
        case 3: return 'three-columns';
        default: return 'four-columns';
      }
    },
    emptyBlockMsg() {
      if (this.component && this.config.components[this.component].empty_block_text !== '') {
        return this.config.components[this.component].empty_block_text;
      }
      return this.msg;
    },
  },
};
