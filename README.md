## Changelog
# v0.1.13 (20200811)
- add & remove lightings, recipes and programs

# v0.1.12 (20200730)
- sync recipe and program with remote controllers

# v0.1.11 (20200728)
- adding run management
- update link lighting

# v0.1.7 (20200727)
- vpn integration + login/logout update + uuid in recipe and program
- checking existing recipes and programs based on uuid


# v0.1 (20200716)
- first working release with remote controle

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

composer require symfony/security-bundle   
sf4 make:user

> Create and edit .env.local to set database path

sf4 make:migration
sf4 doctrine:migrations:migrate

sf4 make:auth
sf4 make:registration-form

> from https://symfony.com/doc/current/security/form_login_setup.html
> from https://symfony.com/doc/current/doctrine/registration_form.html


# VPN

   44  curl -L https://install.pivpn.io | bash
   46  sudo apt-get update
   47  sudo apt-get upgrade
   48  pivpn add