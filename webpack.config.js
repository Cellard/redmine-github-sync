const path = require('path');
// const webpack = require('webpack');

module.exports = {
    resolve: {
        extensions: ['.js', '.json', '.vue'],
        alias: {
            '@': resolve(__dirname, './resources/js')
        }
    },
    output: {
        chunkFilename: 'js/chunks/[name].js',
    },
};
