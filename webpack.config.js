const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');

module.exports = {
    entry: {
        'admin': './assets/js/admin.js',
        'admin.print': './assets/js/admin.print.js',
        'admin.legacy': './assets/js/admin.legacy.js',
        'admin.forms': './assets/js/admin.forms.js',
        'admin.logs.site': './assets/js/admin.logs.site.js'
    },
    output: {
        filename: '[name].min.js',
        path: path.resolve(__dirname, 'assets/js/')
    },
    module: {
        rules: [
            {
                test: /\.(css|scss|sass)$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            url: false
                        }
                    },
                    'postcss-loader',
                    'sass-loader'
                ]
            },
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '../css/[name].min.css'
        }),
    ],
    mode: 'production'
};
