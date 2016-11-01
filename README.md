# Dealerdirect: PHP_CodeSniffer Standards Composer Installer Plugin

[![Travis](https://img.shields.io/travis/DealerDirect/phpcodesniffer-composer-installer.svg?style=flat-square)](https://travis-ci.org/DealerDirect/phpcodesniffer-composer-installer)
[![Dependency Status](https://www.versioneye.com/user/projects/580be0d1d65a7716b613a790/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/580be0d1d65a7716b613a790)
[![Packagist](https://img.shields.io/packagist/dt/dealerdirect/phpcodesniffer-composer-installer.svg?style=flat-square)](https://packagist.org/packages/dealerdirect/phpcodesniffer-composer-installer)
![Maintenance](https://img.shields.io/maintenance/yes/2016.svg?style=flat-square)
![Awesome](https://img.shields.io/badge/awesome%3F-yes-brightgreen.svg?style=flat-square)
[![License](https://img.shields.io/github/license/dealerdirect/phpcodesniffer-composer-installer.svg?style=flat-square)](https://github.com/DealerDirect/phpcodesniffer-composer-installer)

*Keep life simple...*

This composer installer plugin allows for easy installation of [PHP_CodeSniffer] coding standards (rulesets).

No more symbolic linking of directories, checking out repositories on specific locations and/or changing
the `phpcs` configuration.

*Note: This plugin is compatible with both version 2.x and 3.x of
[PHP_CodeSniffer]*

[PHP_CodeSniffer]: https://github.com/squizlabs/PHP_CodeSniffer

## Usage

Add the following lines to your `composer.json` file:

```json
"require-dev": {
   "squizlabs/php_codesniffer": "^2.0.0",
   "dealerdirect/phpcodesniffer-composer-installer" : "*",
   "frenck/php-compatibility": "*"
}
```

## Developing Coding Standards

Coding standard can be developed in the way [PHP_CodeSniffer] documents [this].

Create a composer package of your coding standard by adding a `composer.json` file.

```json
{
  "name" : "acme/phpcodesniffer-our-standards",
  "description" : "Package contains all coding standards of the Acme company",
  "require" : {
    "php" : ">=5.4.0,<8.0.0-dev",
    "squizlabs/php_codesniffer" : "^2.0"
  },
  "type" : "phpcodesniffer-standard"
}
```

Requirements:
* The repository may contain one or more standards. Each in their separate directory in the root of your repository.
* The package `type` must be `phpcodesniffer-standard`. Without this, the plugin will not trigger.

[this]: https://github.com/squizlabs/PHP_CodeSniffer/wiki/Coding-Standard-Tutorial

## Contributing

This is an active open-source project. We are always open to people who want to use the code or contribute to it.

We've set up a separate document for our [contribution guidelines].

Thank you for being involved! :heart_eyes:

[contribution guidelines]: https://github.com/dealerdirect/phpcodesniffer-composer-installer/blob/master/CONTRIBUTING.md

## Authors & Contributors

The original idea and setup of this repository is by [Franck Nijhof], employee @ Dealerdirect.

For a full list off all author and/or contributors, please check [this page].

[this page]: https://github.com/dealerdirect/phpcodesniffer-composer-installer/graphs/contributors
[Franck Nijhof]: https://github.com/frenck

## Would you like to work @ Dealerdirect?

Dealerdirect is always on the looking for energetic and hard working developers and devops engineers.

Interested in working at Dealerdirect? Then please be sure to check out [our vacancies].

Did not find a matching vacancy? Just [get in touch]!

[WorkingAtDealerdirect.eu]

[our vacancies]: http://workingatdealerdirect.eu/?post_type=vacancy&s=&department=99
[get in touch]: http://workingatdealerdirect.eu/open-sollicitatie/
[WorkingAtDealerdirect.eu]: http://www.workingatdealerdirect.eu

## License

The MIT License (MIT)

Copyright (c) 2016 Dealerdirect B.V.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
