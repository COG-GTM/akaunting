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

* PHP 8.1 or higher with the `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `tokenizer`, `xml` and `zip` extensions, plus the PDO driver for your database (e.g. `pdo_mysql`)
* [Composer](https://getcomposer.org) 2
* Node.js 18 or 20 (the asset pipeline does not build on Node 21+ yet)
* Database (e.g.: MariaDB, MySQL, PostgreSQL, SQLite)
* Web Server (eg: Apache, Nginx, IIS)
* [Other libraries](https://akaunting.com/hc/docs/on-premise/requirements/)

## Framework

Akaunting uses [Laravel](http://laravel.com), the best existing PHP framework, as the foundation framework and [Module](https://github.com/akaunting/module) package for Apps.

## Installation

* Clone the repository: `git clone https://github.com/akaunting/akaunting.git`
* Install dependencies: `composer install ; npm install ; npm run dev`

  Before Akaunting is installed, `composer install` prints `SQLSTATE[HY000] [2002]` traces from its `ide-helper:models` step. They are harmless; the install still completes.

* Create an empty database for Akaunting. The installer does not create it, and fails with `Could not connect to the database!` when it is missing:

```bash
mysql -u root -p -e "CREATE DATABASE akaunting"
```

* Install Akaunting (`--db-host` and `--db-port` default to `localhost` and `3306`; pass them if your database runs elsewhere):

```bash
php artisan install --db-name="akaunting" --db-username="root" --db-password="pass" --admin-email="admin@company.com" --admin-password="123456"
```

* Create sample data (optional): `php artisan sample-data:seed`

Point the web server's document root at the project root, not at `public`.
Akaunting serves itself through the `index.php` in the project root and builds
its asset URLs accordingly.

> If you prefer, `php artisan serve` also works for a quick local run; it serves the app at http://127.0.0.1:8000.

Sign in with the admin email and password you passed to `php artisan install`. The first sign-in opens the company setup wizard (Company, Currencies, Ready); fill it in or use "Skip this step" on each page. Leaving the wizard opens the Users page; use the sidebar to reach the dashboard.

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
