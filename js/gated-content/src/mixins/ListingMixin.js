import { SettingsMixin } from '@/mixins/SettingsMixin';

export const ListingMixin = {
  mixins: [SettingsMixin],
  props: {
    viewAll: {
      type: Boolean,
      default: false,
    },
    pagination: {
      type: Boolean,
      default: false,
    },
    limit: {
      type: Number,
      default: 0,
    },
    msg: String,
  },
  data() {
    return {
      component: null,
      listing: [],
      links: {},
    };
  },
  computed: {
    listingIsNotEmpty() {
      return this.listing.length > 0;
    },
    getPageParam() {
      const pageParam = {
        limit: 50,
      };
      if (this.pagination) {
        const currentPage = parseInt(this.$route.query.page, 10) || 0;
        pageParam.limit = this.config.pager_limit;
        pageParam.offset = currentPage * pageParam.limit;
      } else if (this.limit !== 0) {
        pageParam.limit = this.limit;
      }
      return pageParam;
    },
    hasMoreItems() {
      return typeof this.links.next !== 'undefined';
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
