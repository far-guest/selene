const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.sass('src/Assets/scss/index.scss', './src/Assets/dist/css')
    .js('src/Assets/js/index.js', './src/Assets/dist/js')
    .setPublicPath(path.normalize('./'))
