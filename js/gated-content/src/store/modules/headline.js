export default {
  state: {
    headline: {
      title: undefined,
      description: undefined,
      linkUrl: undefined,
      linkText: undefined,
      backgroundImage: undefined,
    },
  },
  actions: {
    setHeadline(context, payload) {
      context.commit('setHeadline', payload);
    },
  },
  mutations: {
    setHeadline(state, payload) {
      state.headline = payload;
    },
  },
  getters: {
    getHeadlineTitle: (state) => state.headline.title,
    getHeadlineDescription: (state) => state.headline.description,
    getHeadlineLinkUrl: (state) => state.headline.linkUrl,
    getHeadlineLinkText: (state) => state.headline.linkText,
    getHeadlineBackgroundImage: (state) => state.headline.backgroundImage,
    isHeadlineEnabled: (state) => {
      const hl = state.headline;
      return !!hl.title || !!hl.description || !!hl.linkText;
    },
  },
};
