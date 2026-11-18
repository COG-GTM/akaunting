# Akaunting™

[![Release](https://img.shields.io/github/v/release/akaunting/akaunting?label=release)](https://github.com/akaunting/akaunting/releases)
![Downloads](https://img.shields.io/github/downloads/akaunting/akaunting/total?label=downloads)
[![Tests](https://img.shields.io/github/actions/workflow/status/akaunting/akaunting/tests.yml?label=tests)](https://github.com/akaunting/akaunting/actions)

Online accounting software designed for small businesses and freelancers. Akaunting is built with modern technologies such as Laravel, VueJS, Tailwind, RESTful API etc. Thanks to its modular structure, Akaunting provides an awesome App Store for users and developers.

* [Home](https://akaunting.com) - The house of Akaunting
* [Forum](https://akaunting.com/forum) - Ask for support
* [Documentation](https://akaunting.com/hc/docs) - Learn how to use
* [Developer Portal](https://developer.akaunting.com) - Generate passive income
* [App Store](https://akaunting.com/apps) - Extend your Akaunting

## Requirements

* PHP 8.1, 8.2 or 8.3 with the extensions listed in `composer.json` (including `bcmath`); the locked dev dependencies do not install on PHP 8.4 yet
* Node.js 18 or 20 (the asset pipeline does not build on Node 21+ yet)
* Database (e.g.: MariaDB, MySQL, PostgreSQL, SQLite)
* Web Server (eg: Apache, Nginx, IIS)
* [Other libraries](https://akaunting.com/hc/docs/on-premise/requirements/)

## Framework

Akaunting uses [Laravel](http://laravel.com), the best existing PHP framework, as the foundation framework and the [Module](https://github.com/akaunting/laravel-module) package for Apps.

See [docs/architecture.md](docs/architecture.md) for how the code is organised: route groups, multi-company scoping, jobs and events, modules and the asset build, with a diagram.

## Installation

* Clone the repository: `git clone https://github.com/COG-GTM/akaunting.git`
* Install dependencies and build the assets: `composer install ; npm install ; npm run dev` (use `npm run production` for minified assets)
* Install Akaunting (needs an empty database; `--db-host` defaults to `localhost` and `--db-port` to `3306`):

```bash
php artisan install --db-name="akaunting" --db-username="root" --db-password="pass" --admin-email="admin@company.com" --admin-password="123456"
```

* Create sample data (optional): `php artisan sample-data:seed`

Point the web server's document root at the project root, not at `public`.
Akaunting serves itself through the `index.php` in the project root and builds
its asset URLs accordingly.

> If you prefer, `php artisan serve` also works for a quick local run.

## Project layout

* `app/` - application code: `Http` (controllers, middleware, requests), `Jobs`, `Events` and `Listeners`, `Models`, `Reports`, `Widgets`, `Console`
* `routes/` - one file per route group (`admin`, `portal`, `api`, `guest`, `wizard`, `signed`, ...)
* `modules/` - installed apps, e.g. `OfflinePayments` and `PaypalStandard`
* `resources/` - Blade views, language files and the Vue/Sass sources under `resources/assets`
* `public/` - compiled assets written by `npm run dev` / `npm run production`
* `database/` - migrations, seeders and factories
* `tests/` - PHPUnit `Feature` and `Unit` tests

## Running tests

Tests use the in-memory SQLite settings in `.env.testing` and need the built assets:

```bash
cp .env.testing .env
npm install && npm run production
composer install
php artisan test --parallel
```

## Contributing

Please, be very clear on your commit messages and Pull Requests, empty Pull Request messages may be rejected without reason.

When contributing code to Akaunting, you must follow the PSR coding standards. The golden rule is: Imitate the existing Akaunting code.

Please note that this project is released with a [Contributor Code of Conduct](https://akaunting.com/conduct). *By participating in this project you agree to abide by its terms*.

## Changelog

Please see [Releases](../../releases) for more information about what has changed recently.

## Security

Please review [our security policy](https://github.com/akaunting/akaunting/security/policy) on how to report security vulnerabilities.

## Credits

* [Denis Duliçi](https://github.com/denisdulici)
* [Cüneyt Şentürk](https://github.com/cuneytsenturk)
* [All Contributors](../../contributors)

## License

Akaunting is released under the [BSL license](LICENSE.txt).
