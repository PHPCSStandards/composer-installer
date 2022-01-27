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

1. Search our repository for open or closed [pull requests][prs] that relate
   to your submission. You don't want to duplicate effort.

2. You may merge the pull request in once you have the sign-off of two other
   developers, or if you do not have permission to do that, you may request
   the second reviewer to merge it for you.

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

[github]: https://github.com/dealerdirect/phpcodesniffer-composer-installer/issues
[prs]: https://github.com/dealerdirect/phpcodesniffer-composer-installer/pulls

## Footnotes

1. All settings needed for the changelog-generator are set in `.github_changelog_generator` file.

2. A convenience script is present at `bin/generate-changelog.sh` that will install the changelog-generator, if it is not present, and run the appropriate `github_changelog_generator` command.
   The script requires BASH to run. It should be run from the project root, similar to `github_changelog_generator`.

[github-changelog-generator]: https://github.com/github-changelog-generator/github-changelog-generator/
[semver]: https://semver.org/
