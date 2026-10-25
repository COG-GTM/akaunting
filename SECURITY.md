# Security Policy

**PLEASE DON'T DISCLOSE SECURITY-RELATED ISSUES PUBLICLY, [SEE BELOW](#reporting-a-vulnerability).**

## Supported Versions

Feel free to report security issues based on the following chart.

| Version | Supported          |
| ------- | ------------------ |
| 1.2.x   | :x:                |
| 1.3.x   | :x:                |
| 2.0.x   | :x:                |
| 2.1.x   | :x: |
| 3.0.x   | :white_check_mark: |

## PHP Runtime

This repository does not ship a Dockerfile, container image or deployment manifest. The PHP build that
actually serves Akaunting is selected by whoever deploys it (distro package, official `php:*` image,
hosting panel, etc.). `composer.json`'s `"php": "^8.1"` is only a dependency constraint; it does not pick
the runtime.

**PHP2Shell (CVE-2026-109618 / CVE-2026-109619)** - two critical unauthenticated RCE bugs in the PHP
engine, fixed on 2026-10-21. Every web SAPI is affected (mod_php, PHP-FPM, php-cgi, FrankenPHP, LiteSpeed).
Run one of these builds or newer, then restart every PHP worker (FPM pool, Apache, queue workers, scheduler):

| PHP branch | First fixed release | Notes |
| ---------- | ------------------- | ----- |
| 8.5        | 8.5.12              | |
| 8.4        | 8.4.27              | |
| 8.3        | 8.3.36              | CI lane |
| 8.2        | 8.2.35              | CI lane |
| 8.1 and older | none             | End-of-life. No fixed php.net release. Use a vendor-backported package (e.g. Ubuntu jammy `php8.1 >= 8.1.2-1ubuntu2.28`) only as a stopgap, and upgrade to 8.2+ |

Floating container tags (`php:8.2-fpm`, `php:8.3-apache`, ...) already resolve to fixed builds, but only after a
`docker pull` / `--pull --no-cache` rebuild and redeploy. Verify with `php -v` (or `php-fpm -v`) on the running host.

Mitigation (reduces exposure, is **not** a fix): set `session.upload_progress.enabled = 0` in `php.ini` or the
FPM pool configuration (not via `ini_set()` or `.user.ini`) and restart the workers.

## Reporting a Vulnerability

If you discover any security related issues, please email security@akaunting.com instead of using the issue tracker. Ömer Çitak ([om3rcitak](https://github.com/om3rcitak)) is responsible of our security.
