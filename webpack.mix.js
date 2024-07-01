const mix = require('laravel-mix');

mix.setPublicPath('./public/');

/*
 |--------------------------------------------------------------------------
 | Login form assets
 |--------------------------------------------------------------------------
 */
mix
    .js('resources/js/login.js', 'js').vue()

    .sass('resources/css/select.scss', 'css')

    .js('resources/js/admin.js', 'js').vue()
    .js('resources/js/partner.js', 'js').vue()
    .js('resources/js/terminal.js', 'js').vue()
    .js('resources/js/controller.js', 'js').vue()
    .js('resources/js/promoter.js', 'js').vue()
    .sass('resources/css/app.scss', 'css')

    .js('resources/js/showcase.js', 'js').vue()
    .js('resources/js/showcase2.js', 'js').vue()
    .js('resources/js/showcase3.js', 'js').vue()
    .js('resources/js/checkout.js', 'js').vue()

    .webpackConfig(require('./webpack.config.js'));

if (mix.inProduction()) {
    mix.version();
}
mix.browserSync('localhost:8000')
mix.disableSuccessNotifications()
