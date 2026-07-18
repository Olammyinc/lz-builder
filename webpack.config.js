const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'lz-builder': './src/index.js',
    },
    output: {
        path: path.resolve(__dirname, 'assets/js/build'),
        filename: '[name].js',
    },
};
