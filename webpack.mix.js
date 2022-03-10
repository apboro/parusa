const mix = require('laravel-mix');

mix.setPublicPath('./public/');

/*
 |--------------------------------------------------------------------------
 | Login form assets
 |--------------------------------------------------------------------------
 */
mix
    .js('resources/js/login.js', 'js').vue()
    .sass('resources/css/login.scss', 'css')
    .sass('resources/css/select.scss', 'css')

    .js('resources/js/admin.js', 'js').vue()
    .js('resources/js/partner.js', 'js').vue()
    .js('resources/js/terminal.js', 'js').vue()
    .sass('resources/css/app.scss', 'css')

    .webpackConfig(require('./webpack.config.js'));

if (mix.inProduction()) {
    mix.version();
}
