# Tiny Issue v2.9.0 - for Laravel 5.1+
[![Build Status](https://travis-ci.org/satrun77/tinyissue.svg?branch=master&?style=flat-square)](https://travis-ci.org/satrun77/tinyissue)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/a6c6ecdf-13f6-4e51-a9f0-f1fffebf1fdd/mini.png)](https://insight.sensiolabs.com/projects/a6c6ecdf-13f6-4e51-a9f0-f1fffebf1fdd)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/satrun77/tinyissue/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/satrun77/tinyissue/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/satrun77/tinyissue/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/satrun77/tinyissue/?branch=master)
[![License](https://img.shields.io/github/license/mashape/apistatus.svg?maxAge=2592000?style=flat-square)](https://github.com/satrun77/tinyissue)
[![GitHub issues](https://img.shields.io/github/issues/satrun77/tinyissue.svg?maxAge=2592000?style=flat-square)](https://github.com/satrun77/tinyissue/issues)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.5-8892BF.svg?style=flat-square)](https://php.net/)
[![Latest Version](https://img.shields.io/github/release/satrun77/tinyissue.svg?style=flat-square)](https://github.com/satrun77/tinyissue/releases)
[![Gitter](https://badges.gitter.im/satrun77/tinyissue.svg)](https://gitter.im/satrun77/tinyissue?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)

# Installation

1. Download and install [composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)
2. Install dependencies `composer install`
3. Create a database. It can be any of the databases supported by Laravel 5.
4. Execute Tiny Issue install command `php artisan tinyissue:install`
5. Note: make sure that all of the Tiny Issues files/folders are behind the public root directory of your site. Except for the ones under public directory.
6. Setup Laravel cronjob see. https://laravel.com/docs/5.1/scheduling

Enjoy!

# Upgrade steps (since version 2.0.0)

1. Pull latest changes from the [GitHub repository](https://github.com/satrun77/tinyissue)
1. Execute Laravel migration command `php artisan migrate`

### How to contribute

We welcome and appreciate all contributions. The `develop` branch is the branch you should base all pull requests and development off of.
The `master` branch is tagged releases only.

Code changes must adhere the PSR-2 standards.

### Main Developers:

#### Version 2.0.0 and above:
- [Mohamed Alsharaf](http://my.geek.nz)

#### Version 1.3.1 and below:
- [Michael Hasselbring](http://michaelhasselbring.com)
- [Zachary Hoover](http://zachoover.com)
- [Suthan Sangaralingham](http://suthanwebs.com/)

