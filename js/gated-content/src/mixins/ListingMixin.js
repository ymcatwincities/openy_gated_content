export const ListingMixin = {
  computed: {
    layoutClass() {
      switch (this.listing.length) {
        case 1: return 'one-columns';
        case 2: return 'two-columns';
        case 3: return 'three-columns';
        default: return 'four-columns';
      }
    },
  },
};
