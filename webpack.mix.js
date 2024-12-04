let mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
    .css('resources/css/app.css', 'public/css')
    .babelConfig({
        presets: ['@babel/preset-env'], // Transpile ES modules
    })
    .webpackConfig({
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /(node_modules)/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['@babel/preset-env'], // Convert modern JS to older JS
                        },
                    },
                },
            ],
        },
    })
    .version()
    .disableNotifications();
