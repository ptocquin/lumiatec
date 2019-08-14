## Log

curl -sS https://get.symfony.com/cli/installer | bash
mv /Users/ptocquin/.symfony/bin/symfony /usr/local/bin/symfony
symfony new --full lumiatec_client <!-- 4.3.3 -->
cd lumiatec_client
gfl init

alias composer='php -d memory_limit=-1 /usr/local/bin/composer'
composer require symfony/webpack-encore-bundle

yarn install
yarn add sass-loader node-sass --dev
yarn add popper.js --dev
yarn add jquery --dev
yarn add bootstrap --dev
yarn add --dev @fortawesome/fontawesome-free

> Edit assets/app.scss
> @import "~bootstrap/scss/bootstrap";
> @import '~@fortawesome/fontawesome-free/scss/fontawesome';
> @import '~@fortawesome/fontawesome-free/scss/regular';
> @import '~@fortawesome/fontawesome-free/scss/solid';
> @import '~@fortawesome/fontawesome-free/scss/brands';

> Edit assets/app.js
> require('../css/app.scss');
> const $ = require('jquery');
> require('bootstrap');
> require('@fortawesome/fontawesome-free/css/regular.min.css');
> require('@fortawesome/fontawesome-free/js/regular.js');

> Enable Encore.enableSassLoader() dans webpack.config.js

yarn encore dev

sf4 make:controller <!-- MainController -->