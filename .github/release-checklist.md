## Release checklist

### General

- [ ] Verify, and if necessary, update the allowed version ranges for various dependencies in the `composer.json` - PR #xxx

### Release Preparation

- [ ] Make sure all closed tickets and PRs have a label.
- [ ] Make sure all closed tickets and PRs are added to the milestone that is to be released.
- [ ] Add changelog for the release to the `CHANGELOG.md` file based on the tickets in the current milestone and submit as PR - PR #xxx
    - 💡 Keep in mind, changelogs are for humans, not for machines.
    - 📝 Remember to add a release diff link at the bottom!
    - 📝 Handy: use this checklist as the PR description to document the release.

### Milestone

- [ ] Close the milestone.
- [ ] Open a new milestone for the next release.
- [ ] If any open PRs/issues which were milestoned for this release did not make it into the release, update their milestone.

### Release

- [ ] Merge the changelog PR.
- [ ] Make sure all CI builds are green.
- [ ] Tag and create a release against the `main` branch and copy & paste the changelog to it.
    - 📝 Use the "Auto-generate changelog" button to get GitHub to create a "New contributors" section and the release diff link. Keep those, remove everything else auto-generated and replace it with the manually crafted changelog for humans.
    - 📝 Check if anything from the link collection at the bottom of the `changelog.md` file needs to be copied in!
- [ ] Make sure all CI builds are green.

### Publicize

- [ ] Toot about the release.
- [ ] Tweet about the release.
