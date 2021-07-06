export const ImageStyleMixin = {
  methods: {
    getStyledUrl(image, imageStyle) {
      let styledUrl = '';
      image.image_style_uri.forEach((item) => {
        if (!item[imageStyle]) { return; }
        styledUrl = item[imageStyle];
      });
      return styledUrl;
    },
  },
};
