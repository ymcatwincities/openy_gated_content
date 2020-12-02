const path = require('path');

module.exports = {
  lintOnSave: false,
  productionSourceMap: false,
  configureWebpack: {
    externals: {
      axios: 'axios',
      'vue-router': 'VueRouter',
      vuex: 'Vuex',
    },
  },
  pluginOptions: {
    'style-resources-loader': {
      preProcessor: 'scss',
      patterns: [path.resolve(__dirname, './src/scss/global.scss')],
    },
  },
  transpileDependencies: [
    /\w+/,
  ],
};
