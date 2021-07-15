import client from '@/client';

export default {
  state: {
    categoriesTree: null,
  },
  actions: {
    async loadCategoriesTree(context) {
      client
        .get('/api/categories-list')
        .then((response) => {
          context.commit('set', response.data);
        })
        .catch((error) => {
          console.error(error);
          throw error;
        });
    },
  },
  mutations: {
    set(state, payload) {
      state.categoriesTree = payload;
    },
  },
  getters: {
    isCategoriesLoaded: (state) => state.categoriesTree !== null,
    getCategoriesTree: (state) => state.categoriesTree,
    getCategoriesByBundle: (state) => (bundle, categoriesData = state.categoriesTree) => (
      categoriesData.filter((category) => category.bundles.some((value) => value === bundle))
    ),
    getSubcategories: (state, getters) => (parent, categoriesData = state.categoriesTree) => (
      categoriesData.reduce((previousValue, categoryData) => {
        if (Number(categoryData.tid) === Number(parent)) {
          return categoryData.children;
        }
        const subtree = getters.getSubcategories(parent, categoryData.children);
        if (subtree.length > 0) {
          return subtree;
        }
        return previousValue;
      }, [])
    ),
    getNestedTids: (state, getters) => (categoriesData = state.categoriesTree) => {
      const tids = [];
      categoriesData.forEach((categoryData) => {
        tids.push(categoryData.tid, ...getters.getNestedTids(categoryData.children));
      });
      return tids;
    },
    getAncestors: (state, getters) => (tid, categoriesData = state.categoriesTree) => (
      categoriesData.reduce((previousValue, categoryData) => {
        if (Number(categoryData.tid) === Number(tid)) {
          return [categoryData];
        }
        const ancestors = getters.getAncestors(tid, categoryData.children);
        if (ancestors.length > 0) {
          return [categoryData, ...ancestors];
        }
        return previousValue;
      }, [])
    ),
  },
};
