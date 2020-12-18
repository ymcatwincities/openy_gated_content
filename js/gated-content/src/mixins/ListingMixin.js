export const ListingMixin = {
  computed: {
    layoutClass() {
      if (this.listing.length === 1) {
        return 'one-columns';
      } if (this.listing.length === 2) {
        return 'two-columns';
      } if (this.listing.length === 3) {
        return 'three-columns';
      }
      return 'four-columns';
    },
  },
};
