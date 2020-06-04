export const JsonApiCombineMixin = {
  methods: {
    // JSON API helper function for singe item, moves data from included to item attributes.
    combine(data, included, params) {
      const item = { ...data };
      if (!included) return item;
      params.forEach((field) => {
        const rel = item.relationships[field].data;
        if (rel === null) {
          item.attributes[field] = null;
          return;
        }
        // Multi-value fields.
        if (Array.isArray(rel)) {
          item.attributes[field] = [];
          rel.forEach((relItem) => {
            item.attributes[field].push(
              included
                .find((obj) => obj.type === relItem.type && obj.id === relItem.id)
                .attributes,
            );
          });
        } else {
          // Single-value fields.
          item.attributes[field] = included
            .find((obj) => obj.type === rel.type && obj.id === rel.id)
            .attributes;
        }
      });

      return item;
    },
    // JSON API helper function for multiple items (listing).
    combineMultiple(data, included, params) {
      const listing = [...data];
      if (!included) return listing;
      listing.forEach((item, key) => {
        listing[key] = this.combine(item, included, params);
      });

      return listing;
    },
  },
};
