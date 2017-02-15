# Dealerdirect: PHP_CodeSniffer Standards Composer Installer Plugin

![Project Stage][project-stage-shield]
![Maintenance][maintenance-shield]
![Awesome][awesome-shield]
[![License][license-shield]](LICENSE.md)

[![Scrutinizer][scrutinizer-shield]][scrutinizer]
[![Dependency Status][versioneye-shield]][versioneye]
[![Latest Version on Packagist][packagist-version-shield]][packagist-version]
[![Packagist][packagist-shield]][packagist]

This composer installer plugin allows for easy installation of [PHP_CodeSniffer][codesniffer] coding standards (rulesets).

No more symbolic linking of directories, checking out repositories on specific locations and/or changing
the `phpcs` configuration.

_Note: This plugin is compatible with both version 2.x and 3.x of_ [PHP_CodeSniffer][codesniffer]

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

Coding standard can be developed in the way [PHP_CodeSniffer][codesniffer] documents [this][tutorial].

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
* The repository may contain one or more standards.
* Each standard can have a separate directory no deeper than 3 levels from the repository root.
* The package `type` must be `phpcodesniffer-standard`. Without this, the plugin will not trigger.

## Contributing

This is an active open-source project. We are always open to people who want to
use the code or contribute to it.

We've set up a separate document for our [contribution guidelines][contributing-guidelines].

Thank you for being involved! :heart_eyes:

## Authors & contributors

The original idea and setup of this repository is by [Franck Nijhof][frenck], employee @ Dealerdirect.

For a full list off all author and/or contributors, please check [this page][contributors].

## Working @ Dealerdirect

Dealerdirect is always on the looking for energetic and hard working developers
and devops engineers.

Interested in working at Dealerdirect?
Then please be sure to check out [our vacancies][vacancies].

Did not find a matching vacancy? Just [get in touch][get-in-touch]!

[workingatdealerdirect.eu][workingatdealerdirecteu]

## License

The MIT License (MIT)

Copyright (c) 2016-2017 Dealerdirect B.V.

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

[project-stage-shield]: https://img.shields.io/badge/Project%20Stage-Development-yellowgreen.svg
[maintenance-shield]: https://img.shields.io/maintenance/yes/2017.svg
[awesome-shield]: https://img.shields.io/badge/awesome%3F-yes-brightgreen.svg
[license-shield]: https://img.shields.io/github/license/dealerdirect/phpcodesniffer-composer-installer.svg
[scrutinizer-shield]: https://img.shields.io/scrutinizer/g/DealerDirect/phpcodesniffer-composer-installer.svg
[scrutinizer]: https://scrutinizer-ci.com/g/DealerDirect/phpcodesniffer-composer-installer/
[versioneye-shield]: https://www.versioneye.com/user/projects/580be0d1d65a7716b613a790/badge.svg
[versioneye]: https://www.versioneye.com/user/projects/580be0d1d65a7716b613a790
[packagist-shield]: https://img.shields.io/packagist/dt/dealerdirect/phpcodesniffer-composer-installer.svg
[packagist]: https://packagist.org/packages/dealerdirect/phpcodesniffer-composer-installer
[packagist-version-shield]: https://img.shields.io/packagist/v/dealerdirect/phpcodesniffer-composer-installer.svg
[packagist-version]: https://packagist.org/packages/dealerdirect/phpcodesniffer-composer-installer
[contribution-guidelines]: CONTRIBUTING.md
[frenck]: https://github.com/frenck
[contributors]: https://github.com/dealerdirect/phpcodesniffer-composer-installer/graphs/contributors
[vacancies]: http://workingatdealerdirect.eu/?post_type=vacancy&s=&department=99
[get-in-touch]: http://workingatdealerdirect.eu/open-sollicitatie/
[workingatdealerdirecteu]: http://www.workingatdealerdirect.eu
[tutorial]: https://github.com/squizlabs/PHP_CodeSniffer/wiki/Coding-Standard-Tutorial
[codesniffer]: https://github.com/squizlabs/PHP_CodeSniffer
