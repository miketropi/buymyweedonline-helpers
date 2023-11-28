const mix = require('laravel-mix');

mix
  .js('src/main.js', 'dist/buymyweedonline-helpers.bundle.js')
  .js('src/admin.js', 'dist/admin.buymyweedonline-helpers.bundle.js')
  .sass('src/scss/main.scss', 'css/buymyweedonline-helpers.bundle.css')
  .sass('src/scss/admin.scss', 'css/admin.buymyweedonline-helpers.bundle.css')
  .setPublicPath('dist')