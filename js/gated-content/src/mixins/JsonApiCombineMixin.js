export const JsonApiCombineMixin = {
  methods: {
    // JSON API helper function for singe item, moves data from included to item attributes.
    combine(data, included, params) {
      const item = { ...data };
      if (!included) return item;
      params.forEach((field) => {
        let fieldName = field;
        if (field.search('.') !== -1) {
          // For sub relationship use child field name.
          // Example - field_gc_category_media.field_media_image = field_media_image
          fieldName = field.split('.').pop();
        }
        const rel = item.relationships[field] && item.relationships[field].data
          ? item.relationships[field].data : null;
        const subRel = params.filter((fieldWithParent) => fieldWithParent.search(`${fieldName}.`) !== -1);
        if (rel === null) {
          item.attributes[field] = null;
          return;
        }
        // Multi-value fields.
        if (Array.isArray(rel)) {
          item.attributes[field] = [];
          rel.forEach((relItem) => {
            const includedItem = included
              .find((obj) => obj.type === relItem.type && obj.id === relItem.id);
            if (includedItem) {
              item.attributes[field].push(includedItem.attributes);

              if (subRel.length > 0) {
                // In case this field contains sub-relationship add this relationship to parent.
                // On next iteration with sub-relationship it well be in rel.
                subRel.forEach((subRelItem) => {
                  const subRelItemName = subRelItem.split('.').pop();
                  item.relationships[field] = includedItem.relationships[subRelItemName]
                    ? includedItem.relationships[subRelItemName] : null;
                });
              }
            }
          });
        } else {
          // Single-value fields.
          const includedItem = included
            .find((obj) => obj.type === rel.type && obj.id === rel.id);
          if (includedItem) {
            item.attributes[field] = includedItem.attributes;

            if (subRel.length > 0) {
              // In case this field contains sub-relationship add this relationship to parent.
              // On next iteration with sub-relationship it well be in rel.
              subRel.forEach((subRelItem) => {
                const subRelItemName = subRelItem.split('.').pop();
                item.relationships[subRelItem] = includedItem.relationships[subRelItemName]
                  ? includedItem.relationships[subRelItemName] : null;
              });
            }
          }
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
