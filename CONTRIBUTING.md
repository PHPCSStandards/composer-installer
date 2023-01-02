# Contributing

When contributing to this repository, please first discuss the change you wish
to make via issue, email, or any other method with the owners of this repository
before making a change.

Please note we have [a code of conduct][], please follow it in all your interactions
with the project.

[a code of conduct]: https://github.com/PHPCSStandards/composer-installer/blob/main/CODE_OF_CONDUCT.md

## Issues and feature requests

You've found a bug in the source code, a mistake in the documentation or maybe
you'd like a new feature? You can help us by submitting an issue to our
[GitHub Repository][github]. Before you create an issue, make sure you search
the archive, maybe your question was already answered.

Even better: You could submit a pull request with a fix / new feature!

## Pull request process

1. Search our repository for open or closed [pull requests][prs] that relate
   to your submission. You don't want to duplicate effort.

2. All pull requests are expected to be accompanied by tests which cover the change.

3. You may merge the pull request in once you have the sign-off of two other
   developers, or if you do not have permission to do that, you may request
   the second reviewer to merge it for you.

### (Code) Quality checks

Every merge-request triggers a build process which runs various checks to help
maintain a quality standard. All JSON, Markdown, PHP, and Yaml files are 
expected to adhere to these quality standards.

These tools fall into two categories: PHP and non-PHP.

### PHP 

The PHP specific tools used by this build are:

- [PHPUnit][] and the [PHPUnit Polyfills][] for the integration tests.
- [PHP_CodeSniffer][] to verify PHP code complies with the [PSR12][] standard.
- [PHPCompatibility][] to verify that code is written in a PHP cross-version compatible manner.
- [PHP-Parallel-Lint][] to check against parse errors in PHP files.
- [PHP-Security-Checker][] to prevent insecure dependencies being installed.

The automated checks with these tools are run via [GitHub Actions][].

As most of these tools are included as Composer `require-dev` packages, they can be
run locally with PHP.

For the Parallel Lint check, the `composer lint` script has been added for convenience.

The Security Checker package is not included in the Composer configuration. An executable
can be downloaded suitable for your operating system from their [releases page][].

Alternatively, these tools can be run using `docker run`, through the Docker
images provided by [Pipeline-Component][].

[PHPUnit]: https://phpunit.de/
[PHPUnit Polyfills]: https://github.com/Yoast/PHPUnit-Polyfills/
[PHP_CodeSniffer]: https://github.com/squizlabs/PHP_CodeSniffer
[PHPCompatibility]: https://github.com/PHPCompatibility/PHPCompatibility
[PHP-Parallel-Lint]: https://github.com/php-parallel-lint/PHP-Parallel-Lint
[PHP-Security-Checker]: https://github.com/fabpot/local-php-security-checker
[PSR12]: https://www.php-fig.org/psr/psr-12/
[releases page]: https://github.com/fabpot/local-php-security-checker/releases/

#### Automated testing

This package includes a test setup for automated testing on all supported PHP versions
using [PHPUnit][] with the [PHPUnit Polyfills][].
This means that tests can be written for the latest version of PHPUnit
(9.x at the time of writing) and still be run on all PHPUnit versions needed to test
all supported PHP versions (PHPUnit 4.x - 9.x).

The tests can be run both via a Composer installed version of PHPUnit, as well as using
a PHPUnit PHAR file, however, whichever way you run the tests, you will always need to
make sure that `composer install` has been run on the repository to make sure the
PHPUnit Polyfills are available.

**Note**: _as these tests run Composer and other CLI commands they will be slow to run._

To run the tests locally:
1. Run `composer install`
2. Run the tests either using a PHPUnit PHAR file or by calling `composer test`.

In case the test setup has trouble locating your `composer.phar` file:

1. Copy the `phpunit.xml.dist` file to `phpunit.xml`.

2. Edit the `phpunit.xml` file and add the following, replacing the value with the applicable path to Composer for your local machine:
    ```xml
    <php>
        <env name="COMPOSER_PHAR" value="path/to/composer.phar"/>
    </php>
    ```
    **Note**: this setting also allows for locally testing with different versions of Composer.
    You could, for instance, have multiple Composer Phar files locally, `composer1.phar`, `composer2.1.phar`, `composer2.2.phar`.
    By changing the path in the value of this `env` setting, you can switch which version will be used in the tests.

### Non-PHP

The non-PHP specific tools used by this build are:

- [jsonlint][] to verify that all JSON files use a consistent code style.
- [remark-lint][] to verify that all markdown files use a consistent code style.
- [yamllint][] to verify that all Yaml files use a consistent code style.

These tools are also run as [GitHub actions][].
All the checks can be run locally using [`act`][].
Alternatively they can be run using `docker run`, as all checks use Docker 
images provided by [Pipeline-Component][].

Finally, they could be run locally using NodeJS, Ruby, PHP, or whatever the tool
is written in. For details please consult the relevant tool's documentation.

[jsonlint]: https://www.npmjs.com/package/jsonlint
[remark-lint]: https://www.npmjs.com/package/remark-lint
[yamllint]: https://yamllint.readthedocs.io/en/stable/
[`act`]: https://github.com/nektos/act

## Release process

To make it possible to automatically generate a changelog, all tickets/issues must have a milestone and at least one label.

A changelog can be generated using the [`github-changelog-generator`][github-changelog-generator].[<sup>(1)</sup>](#footnotes)

Our release versions follow [Semantic Versioning][semver].

New releases (and any related tags) are always created from the `main` branch.

To create a new release:

1. Make sure all closed tickets and MRs have a label.

2. Make sure all closed tickets and MRs are added to the milestone that is to be released.

3. Move any open tickets to the next milestone (create a new one if needed).

4. Generate a changelog by running `github_changelog_generator` in the project root:[<sup>(2)</sup>](#footnotes)
   ```
   github_changelog_generator --future-release "${sVersion}" --header --output --unreleased-only 2>/dev/null
   ```
   Where `sVersion` contains the new version to release.

5. Use GitHub "Draft a new release" functionality to draft a new release (this also creates a tag).

6. Close the milestone for the version that was just released.

[github]: https://github.com/PHPCSStandards/composer-installer/issues
[prs]: https://github.com/PHPCSStandards/composer-installer/pulls
[GitHub Actions]: https://github.com/PHPCSStandards/composer-installer/actions
[Pipeline-Component]: https://pipeline-components.dev/

## Footnotes

1. All settings needed for the changelog-generator are set in `.github_changelog_generator` file.

2. A convenience script is present at `bin/generate-changelog.sh` that will install the changelog-generator, if it is not present, and run the appropriate `github_changelog_generator` command.
   The script requires BASH to run. It should be run from the project root, similar to `github_changelog_generator`.

[github-changelog-generator]: https://github.com/github-changelog-generator/github-changelog-generator/
[semver]: https://semver.org/
