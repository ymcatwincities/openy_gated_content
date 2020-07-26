const path = require('path');

module.exports = () => ({
  entry: './src/logger.js',
  output: {
    filename: 'logger.bundle.min.js',
    path: path.resolve(__dirname, 'dist'),
  },
  externals: {
    axios: 'axios',
    drupal: 'Drupal',
    jquery: 'jQuery',
  },
  module: {
    rules: [
      // {
      //   enforce: 'pre',
      //   test: /\.js$/,
      //   exclude: /node_modules/,
      //   loader: 'eslint-loader',
      //   options: {
      //     cache: true,
      //     fix: true,
      //     failOnError: true,
      //   },
      // },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'babel-loader',
      },
    ],
  },
});
