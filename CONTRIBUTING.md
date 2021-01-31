# Contributing

When contributing to this repository, please first discuss the change you wish
to make via issue, email, or any other method with the owners of this repository
before making a change.

Please note we have [a code of conduct](#code-of-conduct), please follow it in all your interactions
with the project.

## Issues and feature requests

You've found a bug in the source code, a mistake in the documentation or maybe
you'd like a new feature? You can help us by submitting an issue to our
[GitHub Repository][github]. Before you create an issue, make sure you search
the archive, maybe your question was already answered.

Even better: You could submit a pull request with a fix / new feature!

## Pull request process

1. Search our repository for open or closed [pull requests][prs] that relates
   to your submission. You don't want to duplicate effort.

2. You may merge the pull request in once you have the sign-off of two other
   developers, or if you do not have permission to do that, you may request
   the second reviewer to merge it for you.

### (Code) Quality checks

Every merge-request triggers a build process which runs various checks to help
maintain a quality standard. All JSON, Markdown, PHP, and Yaml files are 
expected to adhere to these quality standards.

These tools fall into two categories: PHP and non-PHP.

### PHP 

The PHP specific tools used by this build are:

- [php-codesniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- [php-compatibility](https://github.com/PHPCompatibility/PHPCompatibility) 
- [php-security-checker](https://github.com/sensiolabs/security-checker)

Most of these are run through [Travis CI](https://travis-ci.com/github/Dealerdirect/phpcodesniffer-composer-installer).

As they are included as Composer `require-dev` packages, they can be run locally
with PHP. Alternatively they can be run using `docker run`, through the docker
images provided by [Pipeline-Component](https://pipeline-components.dev/).

### Non-PHP

The non-PHP specific tools used by this build are:

- [jsonlint](https://www.npmjs.com/package/jsonlint)
- [remark-lint](https://www.npmjs.com/package/remark-lint)
- [yamllint](https://yamllint.readthedocs.io/en/stable/)

These tools are run as [GitHub actions](https://docs.github.com/en/actions).
All the checks can be run locally using [`act`](https://github.com/nektos/act)
Alternatively they can be run using `docker run`, as all checks use docker 
images provided by [Pipeline-Component](https://pipeline-components.dev/).

Finally, they could be run locally using NodeJS, Ruby, PHP, or whatever the tool
is written in. For details please consult the relevant tool's documentation.

## Release process

To make it possible to automatically generate a changelog, all tickets/issues must have a milestone and at least one label.

A changelog can be generated using the [`github-changelog-generator`][github-changelog-generator].[<sup>(1)</sup>](#footnotes)

Our release versions follow [Semantic Versioning][semver].

New releases (and any related tags) are always created from the `master` branch.

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

## Code of conduct

### Our pledge

In the interest of fostering an open and welcoming environment, we as
contributors and maintainers pledge to making participation in our project and
our community a harassment-free experience for everyone, regardless of age, body
size, disability, ethnicity, gender identity and expression, level of experience,
nationality, personal appearance, race, religion, or sexual identity and
orientation.

### Our standards

Examples of behavior that contributes to creating a positive environment
include:

- Using welcoming and inclusive language
- Being respectful of differing viewpoints and experiences
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards other community members

Examples of unacceptable behavior by participants include:

- The use of sexualized language or imagery and unwelcome sexual attention or advances
- Trolling, insulting/derogatory comments, and personal or political attacks
- Public or private harassment
- Publishing others' private information, such as a physical or electronic address, without explicit permission
- Other conduct which could reasonably be considered inappropriate in a professional setting

### Our responsibilities

Project maintainers are responsible for clarifying the standards of acceptable
behavior and are expected to take appropriate and fair corrective action in
response to any instances of unacceptable behavior.

Project maintainers have the right and responsibility to remove, edit, or
reject comments, commits, code, wiki edits, issues, and other contributions
that are not aligned to this Code of Conduct, or to ban temporarily or
permanently any contributor for other behaviors that they deem inappropriate,
threatening, offensive, or harmful.

### Scope

This Code of Conduct applies both within project spaces and in public spaces
when an individual is representing the project or its community. Examples of
representing a project or community include using an official project e-mail
address, posting via an official social media account, or acting as an appointed
representative at an online or offline event. Representation of a project may be
further defined and clarified by project maintainers.

### Enforcement

Instances of abusive, harassing, or otherwise unacceptable behavior may be
reported by contacting the project team at franck.nijhof@dealerdirect.com. All
complaints will be reviewed and investigated and will result in a response that
is deemed necessary and appropriate to the circumstances. The project team is
obligated to maintain confidentiality with regard to the reporter of an incident.
Further details of specific enforcement policies may be posted separately.

Project maintainers who do not follow or enforce the Code of Conduct in good
faith may face temporary or permanent repercussions as determined by other
members of the project's leadership.

### Attribution

This Code of Conduct is adapted from the [Contributor Covenant][homepage],
version 1.4, available at [http://contributor-covenant.org/version/1/4][version]

[homepage]: http://contributor-covenant.org
[version]: http://contributor-covenant.org/version/1/4/
[github]: https://github.com/dealerdirect/phpcodesniffer-composer-installer/issues
[prs]: https://github.com/dealerdirect/phpcodesniffer-composer-installer/pulls

## Footnotes

1. All settings needed for the changelog-generator are set in `.github_changelog_generator` file.

2. A convenience script is present at `bin/generate-changelog.sh` that will install the changelog-generator, if it is not present, and run the appropriate `github_changelog_generator` command.
   The script requires BASH to run. It should be run from the project root, similar to `github_changelog_generator`.

[github-changelog-generator]: https://github.com/github-changelog-generator/github-changelog-generator/
[semver]: https://semver.org/
