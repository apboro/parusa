const mix = require('laravel-mix');

mix.setPublicPath('./public/');

/*
 |--------------------------------------------------------------------------
 | Login form assets
 |--------------------------------------------------------------------------
 */
mix.js('resources/js/login.js', 'js')
    .vue()
    .sass('resources/css/login.scss', 'css')

    .sass('resources/css/app.scss', 'css')

    .js('resources/js/admin.js', 'js')
    .vue()

    .webpackConfig(require('./webpack.config'));

if (mix.inProduction()) {
    mix.version();
}
