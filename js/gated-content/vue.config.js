const path = require('path');

module.exports = {
  lintOnSave: false,
  configureWebpack: {
    externals: {
      axios: 'axios',
      'bootstrap-vue': 'BootstrapVue',
      'vue-router': 'VueRouter',
      vuex: 'Vuex',
      'vuex-persist': 'VuexPersistence',
    },
  },
  pluginOptions: {
    'style-resources-loader': {
      preProcessor: 'scss',
      patterns: [path.resolve(__dirname, './src/scss/global.scss')],
    },
  },
};
