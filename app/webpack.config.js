const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

module.exports = (env) => {
    return {
        entry: './src/main.js',
        output: {
            filename: 'app.js',
            path: path.resolve(__dirname, 'www/js'),
            sourceMapFilename: 'app.js.map'
        },
        devtool: 'source-map',
        module: {
            rules: [
                {
                    test: /\.vue$/,
                    use: 'vue-loader'
                },
                {
                    test: /\.(sa|sc|c)ss$/,
                    use: ['vue-style-loader', 'css-loader', 'sass-loader']
                },
                {
                    test: /\.(ttf|eot|svg|woff(2)?)(\?[a-z0-9=&.]+)?$/,
                    use: 'file-loader'
                }
            ]
        },
        plugins: [
            new VueLoaderPlugin()
        ]
    };
};
