const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');


module.exports = {
    entry: './assets/js/admin.js',
    output: {
        filename: 'admin.min.js',
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
            filename: '../css/admin.min.css'
        })
    ],
    mode: 'production'
};
