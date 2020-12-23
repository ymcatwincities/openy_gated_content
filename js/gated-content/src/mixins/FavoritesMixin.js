export const FavoritesMixin = {
  props: {
    favorites: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    favoritesList() {
      return this.$store.getters.getFavoritesList;
    },
  },
  methods: {
    getFavoritesTypeIds(entityType, bundle) {
      const ids = [];
      this.favoritesList[entityType][bundle].forEach((item) => {
        ids.push(item.entity_id);
      });
      return ids;
    },
    isFavoritesTypeEmpty(entityType, bundle) {
      return typeof (this.favoritesList[entityType]) === 'undefined'
        || typeof (this.favoritesList[entityType][bundle]) === 'undefined'
        || this.favoritesList[entityType][bundle].length === 0;
    },
  },
};
