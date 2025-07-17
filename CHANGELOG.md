# Change Log for the Composer Installer for PHP CodeSniffer

All notable changes to this project will be documented in this file.

This projects adheres to [Keep a CHANGELOG](https://keepachangelog.com/) and uses [Semantic Versioning](https://semver.org/).


## [Unreleased]

_Nothing yet._


## [v1.1.2] - 2025-07-17

### Changed
- General housekeeping.

### Fixed
- [#247]: Potential fatal error when the Composer EventDispatcher is called programmatically from an integration. Thanks [@jrfnl] ! [#248]

[#247]:  https://github.com/PHPCSStandards/composer-installer/issues/247
[#248]:  https://github.com/PHPCSStandards/composer-installer/pull/248


## [v1.1.1] - 2025-06-27

### Changed
- Various housekeeping, including improvements to the documentation.

### Fixed
- [#239]: The PHP_CodeSniffer package could not be always found when running the plugin in a Drupal or Magento setup. Thanks [@jrfnl] ! [#245]

[#239]:  https://github.com/PHPCSStandards/composer-installer/issues/239
[#245]:  https://github.com/PHPCSStandards/composer-installer/pull/245


## [v1.1.0] - 2025-06-24

### Changed
- Various housekeeping, including improvements to the documentation and tests. Thanks [@SplotyCode], [@fredden] for contributing!

### Removed
- Drop support for Composer v1.x. Thanks [@fredden] ! [#230]

[#230]:  https://github.com/PHPCSStandards/composer-installer/pull/230


## [v1.0.0] - 2023-01-05

### Breaking changes
- Rename namespace prefix from Dealerdirect to PHPCSStandards by [@jrfnl] in [#191]
- Drop support for PHP 5.3 by [@jrfnl] in [#147]

### Changed
- Correct grammar in error message by [@fredden] in [#189]
- .gitattributes: sync with current repo state by [@jrfnl] in [#198]
- PHPCSVersions: update URL references by [@jrfnl] in [#161]
- README: remove references to Scrutinizer by [@jrfnl] in [#157]
- Rename references to master branch by [@Potherca] in [#201]
- Update repo references by [@jrfnl] in [#158]
- GH Actions: add builds against Composer 2.2 for PHP 7.2 - 8.x by [@jrfnl] in [#172]
- GH Actions: bust the cache semi-regularly by [@jrfnl] in [#192]
- GH Actions: fix builds on Windows with PHP 8.2 by [@jrfnl] in [#180]
- GH Actions: fix up fail-fast for setup-php by [@jrfnl] in [#195]
- GH Actions: run integration tests against Composer snapshot by [@jrfnl] in [#163]
- GH Actions: run linting against against ubuntu-latest by [@jrfnl] in [#184]
- GH Actions/Securitycheck: update the security checker download by [@jrfnl] in [#178]
- GH Actions/Securitycheck: update the security checker download by [@jrfnl] in [#186]
- GH Actions/Securitycheck: update the security checker download by [@jrfnl] in [#190]
- GH Actions: selectively use fail-fast with setup-php by [@jrfnl] in [#194]
- GH Actions: stop running tests against PHP 5.5/Composer 1.x on Windows (and remove work-arounds) by [@jrfnl] in [#183]
- GH Actions: various tweaks / PHP 8.2 not allowed to fail by [@jrfnl] in [#193]
- GH Actions: version update for various predefined actions by [@jrfnl] in [#170]
- Update YamLint by [@Potherca] in [#173]
- Add initial integration test setup and first few tests by [@jrfnl] in [#153]
- BaseLineTest: stabilize the message checks by [@jrfnl] in [#162]
- PlayNiceWithScriptsTest: wrap output expectation in condition by [@jrfnl] in [#179]
- RegisterExternalStandardsTest: add new tests by [@jrfnl] in [#165]
- RegisterExternalStandardsTest: stabilize test for Composer v1 on Windows with PHP 5.5 by [@jrfnl] in [#171]
- TestCase::executeCliCommand(): retry Composer commands on a particular exception by [@jrfnl] in [#164]
- Tests: add new InstalledPathsOrderTest by [@jrfnl] in [#176]
- Tests: add new InstallUpdateEventsTest and NonInstallUpdateEventsTest by [@jrfnl] in [#174]
- Tests: add new InvalidPackagesTest by [@jrfnl] in [#168]
- Tests: add new PlayNiceWithScriptsTest by [@jrfnl] in [#169]
- Tests: add new PreexistingPHPCSConfigTest by [@jrfnl] in [#166]
- Tests: add new PreexistingPHPCSInstalledPathsConfigTest + bug fix by [@jrfnl] in [#167]
- Tests: add new RemovePluginTest by [@jrfnl] in [#177]
- Tests: add new RootPackageHandlingTest + bugfix by [@jrfnl] in [#175]

### Fixed
- Plugin: improve feedback by [@jrfnl] in [#182]

[#147]:  https://github.com/PHPCSStandards/composer-installer/pull/147
[#153]:  https://github.com/PHPCSStandards/composer-installer/pull/153
[#157]:  https://github.com/PHPCSStandards/composer-installer/pull/157
[#158]:  https://github.com/PHPCSStandards/composer-installer/pull/158
[#161]:  https://github.com/PHPCSStandards/composer-installer/pull/161
[#162]:  https://github.com/PHPCSStandards/composer-installer/pull/162
[#163]:  https://github.com/PHPCSStandards/composer-installer/pull/163
[#164]:  https://github.com/PHPCSStandards/composer-installer/pull/164
[#165]:  https://github.com/PHPCSStandards/composer-installer/pull/165
[#166]:  https://github.com/PHPCSStandards/composer-installer/pull/166
[#167]:  https://github.com/PHPCSStandards/composer-installer/pull/167
[#168]:  https://github.com/PHPCSStandards/composer-installer/pull/168
[#169]:  https://github.com/PHPCSStandards/composer-installer/pull/169
[#170]:  https://github.com/PHPCSStandards/composer-installer/pull/170
[#171]:  https://github.com/PHPCSStandards/composer-installer/pull/171
[#172]:  https://github.com/PHPCSStandards/composer-installer/pull/172
[#173]:  https://github.com/PHPCSStandards/composer-installer/pull/173
[#174]:  https://github.com/PHPCSStandards/composer-installer/pull/174
[#175]:  https://github.com/PHPCSStandards/composer-installer/pull/175
[#176]:  https://github.com/PHPCSStandards/composer-installer/pull/176
[#177]:  https://github.com/PHPCSStandards/composer-installer/pull/177
[#178]:  https://github.com/PHPCSStandards/composer-installer/pull/178
[#179]:  https://github.com/PHPCSStandards/composer-installer/pull/179
[#180]:  https://github.com/PHPCSStandards/composer-installer/pull/180
[#182]:  https://github.com/PHPCSStandards/composer-installer/pull/182
[#183]:  https://github.com/PHPCSStandards/composer-installer/pull/183
[#184]:  https://github.com/PHPCSStandards/composer-installer/pull/184
[#186]:  https://github.com/PHPCSStandards/composer-installer/pull/186
[#189]:  https://github.com/PHPCSStandards/composer-installer/pull/189
[#190]:  https://github.com/PHPCSStandards/composer-installer/pull/190
[#191]:  https://github.com/PHPCSStandards/composer-installer/pull/191
[#192]:  https://github.com/PHPCSStandards/composer-installer/pull/192
[#193]:  https://github.com/PHPCSStandards/composer-installer/pull/193
[#194]:  https://github.com/PHPCSStandards/composer-installer/pull/194
[#195]:  https://github.com/PHPCSStandards/composer-installer/pull/195
[#198]:  https://github.com/PHPCSStandards/composer-installer/pull/198
[#201]:  https://github.com/PHPCSStandards/composer-installer/pull/201


## [v0.7.2] - 2022-02-04

### Changed
- Add details regarding QA automation in CONTRIBUTING.md file. by [@Potherca] in [#133]
- Add mention of Composer and PHP compatibility to project README. by [@Potherca] in [#132]
- Composer: tweak PHPCS version constraint by [@jrfnl] in [#152]
- CONTRIBUTING: remove duplicate code of conduct by [@jrfnl] in [#148]
- Document release process by [@Potherca] in [#118]
- Plugin::loadInstalledPaths(): config-show always shows all by [@jrfnl] in [#154]
- README: minor tweaks by [@jrfnl] in [#149]
- README: update with information about Composer >= 2.2 by [@jrfnl] in [#141]
- Replace deprecated Sensiolabs security checker by [@paras-malhotra] in [#130]
- Stabilize a condition by [@jrfnl] in [#127]
- Update copyright year by [@jrfnl] in [#138]
- Various minor tweaks by [@jrfnl] in [#151]
- Change YamlLint config to prevent "truthy" warning. by [@Potherca] in [#144]
- GH Actions: PHP 8.1 has been released by [@jrfnl] in [#139]
- Travis: line length tweaks by [@jrfnl] in [#128]
- CI: Switch to GH Actions by [@jrfnl] in [#137]
- CI: various updates by [@jrfnl] in [#140]

[#118]:  https://github.com/PHPCSStandards/composer-installer/pull/118
[#127]:  https://github.com/PHPCSStandards/composer-installer/pull/127
[#128]:  https://github.com/PHPCSStandards/composer-installer/pull/128
[#130]:  https://github.com/PHPCSStandards/composer-installer/pull/130
[#132]:  https://github.com/PHPCSStandards/composer-installer/pull/132
[#133]:  https://github.com/PHPCSStandards/composer-installer/pull/133
[#137]:  https://github.com/PHPCSStandards/composer-installer/pull/137
[#138]:  https://github.com/PHPCSStandards/composer-installer/pull/138
[#139]:  https://github.com/PHPCSStandards/composer-installer/pull/139
[#140]:  https://github.com/PHPCSStandards/composer-installer/pull/140
[#141]:  https://github.com/PHPCSStandards/composer-installer/pull/141
[#144]:  https://github.com/PHPCSStandards/composer-installer/pull/144
[#148]:  https://github.com/PHPCSStandards/composer-installer/pull/148
[#149]:  https://github.com/PHPCSStandards/composer-installer/pull/149
[#151]:  https://github.com/PHPCSStandards/composer-installer/pull/151
[#152]:  https://github.com/PHPCSStandards/composer-installer/pull/152
[#154]:  https://github.com/PHPCSStandards/composer-installer/pull/154


## [v0.7.1] - 2020-12-07

### Closed issues
- Order of installed_paths inconsistent between runs [#125]
- Maintaining this project and Admin rights [#113]

### Changed
- Sort list of installed paths before saving for consistency by [@kevinfodness] in [#126]
- Update code of conduct by [@Potherca] in [#117]
- Add remark configuration by [@Potherca] in [#122]
- Travis: add build against PHP 8.0 by [@jrfnl] in [#124]

### Fixed
- Fixed v4 constraint by [@GrahamCampbell] in [#115]

[#113]:  https://github.com/PHPCSStandards/composer-installer/issues/113
[#115]:  https://github.com/PHPCSStandards/composer-installer/pull/115
[#117]:  https://github.com/PHPCSStandards/composer-installer/pull/117
[#122]:  https://github.com/PHPCSStandards/composer-installer/pull/122
[#124]:  https://github.com/PHPCSStandards/composer-installer/pull/124
[#125]:  https://github.com/PHPCSStandards/composer-installer/issues/125
[#126]:  https://github.com/PHPCSStandards/composer-installer/pull/126


## [v0.7.0] - 2020-06-25

### Closed issues
- Composer 2.x compatibility [#108]
- Add link to Packagist on main page [#110]
- Switch from Travis CI .org to .com [#112]

### Added
- Allow installation on PHP 8 by [@jrfnl] in [#106]
- Support Composer 2.0 by [@jrfnl] in [#111]

### Changed
- Test with PHPCS 4.x and allow installation when using PHPCS 4.x by [@jrfnl] in [#107]
- Fix case of class name by [@Seldaek] in [#109]

[#106]:  https://github.com/PHPCSStandards/composer-installer/pull/106
[#107]:  https://github.com/PHPCSStandards/composer-installer/pull/107
[#108]:  https://github.com/PHPCSStandards/composer-installer/issues/108
[#109]:  https://github.com/PHPCSStandards/composer-installer/pull/109
[#110]:  https://github.com/PHPCSStandards/composer-installer/issues/110
[#111]:  https://github.com/PHPCSStandards/composer-installer/pull/111
[#112]:  https://github.com/PHPCSStandards/composer-installer/issues/112


## [v0.6.2] - 2020-01-29

### Fixed
- Composer scripts/commands broken in 0.6.0 update by [@BrianHenryIE] in [#105]

[#105]:  https://github.com/PHPCSStandards/composer-installer/pull/105

## [v0.6.1] - 2020-01-27

### Closed issues
- Do not exit with code 1 on uninstall (--no-dev) [#103]

### Changed
- Readme: minor tweak now 0.6.0 has been released [#102] ([@jrfnl])

### Fixed
- [#103]: Fix for issue #103 [#104] ([@Potherca])

[#102]:  https://github.com/PHPCSStandards/composer-installer/pull/102
[#103]:  https://github.com/PHPCSStandards/composer-installer/issues/103
[#104]:  https://github.com/PHPCSStandards/composer-installer/pull/104


## [v0.6.0] - 2020-01-19

### Closed issues
- Composer PHP version appears not to be respected [#79]
- Allow a string value for extra.phpcodesniffer-search-depth [#82]
- Add [@jrfnl] as (co)maintainer to this project [#87]

### Added
- Add support for a string phpcodesniffer-search-depth config value set via composer config by [@TravisCarden] in [#85]
- Send an exit code when the script terminates by [@jrfnl] in [#93]
- Verify the installed_paths after save by [@jrfnl] in [#97]

### Changed
- CS: fix compliance with PSR12 by [@jrfnl] in [#88]
- Improve GH issue template by [@jrfnl] in [#94]
- Readme: add section about including this plugin from an external PHPCS standard by [@jrfnl] in [#95]
- Bug report template: further enhancement by [@jrfnl] in [#99]
- Update copyright year. by [@Potherca] in [#101]
- Adding linting jobs in github action by [@mjrider] in [#96]
- GH Actions: minor tweaks: by [@jrfnl] in [#100]
- Travis: disable Xdebug by [@jrfnl] in [#89]
- Travis: test against PHP 7.4, not snapshot by [@jrfnl] in [#90]
- Travis: use a mix of PHPCS versions in the matrix by [@jrfnl] in [#91]
- Update Travis file and fix build by [@Potherca] in [#86]

### Fixed
- [#79]: Respect PHP version used by Composer and provide better feedback on failure by [@jrfnl] in [#80]
- Bug fix: loadInstalledPaths() very very broken since PHPCS 3.1.0 by [@jrfnl] in [#98]

[#79]:  https://github.com/PHPCSStandards/composer-installer/issues/79
[#80]:  https://github.com/PHPCSStandards/composer-installer/issues/80
[#82]:  https://github.com/PHPCSStandards/composer-installer/issues/82
[#85]:  https://github.com/PHPCSStandards/composer-installer/pull/85
[#86]:  https://github.com/PHPCSStandards/composer-installer/pull/86
[#87]:  https://github.com/PHPCSStandards/composer-installer/issues/87
[#88]:  https://github.com/PHPCSStandards/composer-installer/pull/88
[#89]:  https://github.com/PHPCSStandards/composer-installer/pull/89
[#90]:  https://github.com/PHPCSStandards/composer-installer/pull/90
[#91]:  https://github.com/PHPCSStandards/composer-installer/pull/91
[#93]:  https://github.com/PHPCSStandards/composer-installer/pull/93
[#94]:  https://github.com/PHPCSStandards/composer-installer/pull/94
[#95]:  https://github.com/PHPCSStandards/composer-installer/pull/95
[#96]:  https://github.com/PHPCSStandards/composer-installer/pull/96
[#97]:  https://github.com/PHPCSStandards/composer-installer/pull/97
[#98]:  https://github.com/PHPCSStandards/composer-installer/issues/98
[#99]:  https://github.com/PHPCSStandards/composer-installer/pull/99
[#100]: https://github.com/PHPCSStandards/composer-installer/pull/100
[#101]: https://github.com/PHPCSStandards/composer-installer/pull/101


## [v0.5.0] - 2018-10-26

### Closed issues
- Scan depth as parameter [#45]
- phpcs: Exit Code: 127 (Command not found) on every Composer command [#48]
- The composer plugin implementation seems to be breaking the composer lifecycle [#49]
- Installation error [#53]
- Broke composer commands when used with wp-cli/package-command [#59]
- Getting a new stable release [#60]
- Support PHP CodeSniffer standards in packages installed outside of the vendor directory [#63]

### Added
- Adds the ability to set the max depth from the composer.json file by [@Potherca] in [#46]

### Changed
- Build/PHPCS: update PHPCompatibility repo name by [@jrfnl] in [#54]
- README: remove VersionEye badge by [@jrfnl] in [#55]
- README: replace maintenance badge by [@jrfnl] in [#56]
- Execute phpcs and security-checker from vendor/bin by [@gapple] in [#52]
- PHPCS: various minor tweaks by [@jrfnl] in [#57]
- Travis: various tweaks by [@jrfnl] in [#58]
- Use PHPCompatibility 9.0.0 by [@jrfnl] in [#61]
- Build/Travis: test builds against PHP 7.3 by [@jrfnl] in [#62]
- Updates copyright year by [@frenck] in [#67]
- Enforces PSR12 by [@frenck] in [#66]
- Updates contact information by [@frenck] in [#68]
- Updates README, spelling/grammar, removed Working section by [@frenck] in [#69]
- Replaces ProcessBuilder by ProcessExecutor by [@frenck] in [#70]
- Refactors relative path logic by [@frenck] in [#71]
- Removes suggested packages by [@frenck] in [#72]
- Ensures absolute paths during detection phase by [@frenck] in [#73]
- Trivial code cleanup by [@frenck] in [#74]
- Fixes duplicate declaration of cwd by [@frenck] in [#75]
- Removes HHVM from TravisCI by [@frenck] in [#76]
- Adds PHP_CodeSniffer version constraints by [@frenck] in [#77]

### Fixed
- [#49]: Move loadInstalledPaths from init to onDependenciesChangedEvent by [@gapple] in [#51]

[#45]: https://github.com/PHPCSStandards/composer-installer/issues/45
[#46]: https://github.com/PHPCSStandards/composer-installer/pull/46
[#48]: https://github.com/PHPCSStandards/composer-installer/issues/48
[#49]: https://github.com/PHPCSStandards/composer-installer/issues/49
[#51]: https://github.com/PHPCSStandards/composer-installer/pull/51
[#52]: https://github.com/PHPCSStandards/composer-installer/pull/52
[#53]: https://github.com/PHPCSStandards/composer-installer/issues/53
[#54]: https://github.com/PHPCSStandards/composer-installer/pull/54
[#55]: https://github.com/PHPCSStandards/composer-installer/pull/55
[#56]: https://github.com/PHPCSStandards/composer-installer/pull/56
[#57]: https://github.com/PHPCSStandards/composer-installer/pull/57
[#58]: https://github.com/PHPCSStandards/composer-installer/pull/58
[#59]: https://github.com/PHPCSStandards/composer-installer/issues/59
[#60]: https://github.com/PHPCSStandards/composer-installer/issues/60
[#61]: https://github.com/PHPCSStandards/composer-installer/pull/61
[#62]: https://github.com/PHPCSStandards/composer-installer/pull/62
[#63]: https://github.com/PHPCSStandards/composer-installer/issues/63
[#66]: https://github.com/PHPCSStandards/composer-installer/pull/66
[#67]: https://github.com/PHPCSStandards/composer-installer/pull/67
[#68]: https://github.com/PHPCSStandards/composer-installer/pull/68
[#69]: https://github.com/PHPCSStandards/composer-installer/pull/69
[#70]: https://github.com/PHPCSStandards/composer-installer/pull/70
[#71]: https://github.com/PHPCSStandards/composer-installer/pull/71
[#72]: https://github.com/PHPCSStandards/composer-installer/pull/72
[#73]: https://github.com/PHPCSStandards/composer-installer/pull/73
[#74]: https://github.com/PHPCSStandards/composer-installer/pull/74
[#75]: https://github.com/PHPCSStandards/composer-installer/pull/75
[#76]: https://github.com/PHPCSStandards/composer-installer/pull/76
[#77]: https://github.com/PHPCSStandards/composer-installer/pull/77


## [v0.4.4] - 2017-12-06

### Closed issues
- PHP 7.2 compatibility issue [#43]

### Changed
- Update Travis CI svg badge and link URLs [#42] ([@ntwb])
- Add PHP 7.2 to Travis CI [#41] ([@ntwb])
- Docs: Fix link to releases [#40] ([@GaryJones])

[#40]: https://github.com/PHPCSStandards/composer-installer/pull/40
[#41]: https://github.com/PHPCSStandards/composer-installer/pull/41
[#42]: https://github.com/PHPCSStandards/composer-installer/pull/42
[#43]: https://github.com/PHPCSStandards/composer-installer/issues/43


## [v0.4.3] - 2017-09-18

### Changed
- CS: Add PHP 5.3 compatibility [#39] ([@GaryJones])
- Local PHPCS [#38] ([@GaryJones])

[#38]: https://github.com/PHPCSStandards/composer-installer/pull/38
[#39]: https://github.com/PHPCSStandards/composer-installer/pull/39


## [v0.4.2] - 2017-08-16

### Changed
- Docs: Rename example script [#35] ([@GaryJones])
- Update README.md [#36] ([@jrfnl])
- Documentation update. [#37] ([@frenck])

[#35]: https://github.com/PHPCSStandards/composer-installer/pull/35
[#36]: https://github.com/PHPCSStandards/composer-installer/pull/36
[#37]: https://github.com/PHPCSStandards/composer-installer/pull/37


## [v0.4.1] - 2017-08-01

### Closed issues
- Incorrect relative paths for WPCS [#33]

### Fixed
- [#33]: Changes the way the installed_paths are set. [#34] ([@frenck])

[#33]: https://github.com/PHPCSStandards/composer-installer/issues/33
[#34]: https://github.com/PHPCSStandards/composer-installer/pull/34


## [v0.4.0] - 2017-05-11

### Closed issues
- Add support for code standards in root of repository for PHP_CodeSniffer 3.x [#26]
- Config codings styles in composer.json from project [#23]
- Check the root package for sniffs to install [#20]
- Document the ability to execute the main plugin functionality directly [#18]
- Add a CHANGELOG.md [#17]
- Install sniffs with relative paths in CodeSniffer.conf [#14]

### Added
- Support for coding standard in the root repository for PHP_CodeSniffer v3.x [#30] ([@frenck])
- Added support for having coding standards in the root package [#25] ([@frenck])

### Changed
- Local projects uses relative paths to their coding standards [#28] ([@frenck])
- Docs: Updated README. [#31] ([@frenck])
- Docs: Adds reference to calling the script directly in the README. [#29] ([@Potherca])
- Adds Travis-CI configuration file. [#27] ([@Potherca])


[#14]: https://github.com/PHPCSStandards/composer-installer/issues/14
[#17]: https://github.com/PHPCSStandards/composer-installer/issues/17
[#18]: https://github.com/PHPCSStandards/composer-installer/issues/18
[#20]: https://github.com/PHPCSStandards/composer-installer/issues/20
[#23]: https://github.com/PHPCSStandards/composer-installer/issues/23
[#25]: https://github.com/PHPCSStandards/composer-installer/pull/25
[#26]: https://github.com/PHPCSStandards/composer-installer/issues/26
[#27]: https://github.com/PHPCSStandards/composer-installer/pull/27
[#28]: https://github.com/PHPCSStandards/composer-installer/pull/28
[#29]: https://github.com/PHPCSStandards/composer-installer/pull/29
[#31]: https://github.com/PHPCSStandards/composer-installer/pull/31


## [v0.3.2] - 2017-03-29

### Closed issues
- Coding Standard tries itself to install with installPath when it's the root package [#19]

### Changed
- Improvements to the documentation [#22] ([@Potherca])
- Added instanceof check to prevent root package from being installed [#21] ([@bastianschwarz])

### Fixed
- [#13]: Incorrect coding standards search depth [#15] ([@frenck])

[#19]: https://github.com/PHPCSStandards/composer-installer/issues/19
[#21]: https://github.com/PHPCSStandards/composer-installer/pull/21
[#22]: https://github.com/PHPCSStandards/composer-installer/pull/22


## [v0.3.1] - 2017-02-17

### Closed issues
- Plugin not working correctly when sniffs install depth is equal to "1" [#13]
- Create new stable release version to support wider use [#11]

### Fixed
- [#13]: Incorrect coding standards search depth [#15] ([@frenck])

[#11]: https://github.com/PHPCSStandards/composer-installer/issues/11
[#13]: https://github.com/PHPCSStandards/composer-installer/issues/13
[#15]: https://github.com/PHPCSStandards/composer-installer/pull/15


## [v0.3.0] - 2017-02-15

### Implemented enhancements
- Install Plugin provides no feedback [#7]
- Installing coding standards when executing Composer with --no-scripts [#4]
- Github contribution templates [#10] ([@christopher-hopper])
- Show config actions and a result as Console output [#8] ([@christopher-hopper])
- Adds static function to call the Plugin::onDependenciesChangedEvent() method [#5] ([@Potherca])

###  Added
- Support existing standards packages with subfolders [#6] ([@christopher-hopper])

### Changed
- Improved documentation [#12] ([@frenck])
- Removal of lgtm.co [#3] ([@frenck])

[#3]:  https://github.com/PHPCSStandards/composer-installer/pull/3
[#4]:  https://github.com/PHPCSStandards/composer-installer/issues/4
[#5]:  https://github.com/PHPCSStandards/composer-installer/pull/5
[#6]:  https://github.com/PHPCSStandards/composer-installer/pull/6
[#7]:  https://github.com/PHPCSStandards/composer-installer/issues/7
[#8]:  https://github.com/PHPCSStandards/composer-installer/pull/8
[#10]: https://github.com/PHPCSStandards/composer-installer/pull/10
[#12]: https://github.com/PHPCSStandards/composer-installer/pull/12


## [v0.2.1] - 2016-11-01

Fixes an issue with having this plugin installed globally within composer, but using your global composer installation on a local repository without PHP_CodeSniffer installed.

### Fixed
- Bugfix: Plugin fails when PHP_CodeSniffer is not installed [#2] ([@frenck])

[#2]: https://github.com/PHPCSStandards/composer-installer/pull/2


## [v0.2.0] - 2016-11-01

For this version on, this installer no longer messes with the installation paths of composer libraries, but instead, it configures PHP_CodeSniffer to look into other directories for coding standards.

### Changed
- PHPCS Configuration management [#1] ([@frenck])

[#1]: https://github.com/PHPCSStandards/composer-installer/pull/1


## [v0.1.1] - 2016-10-24

### Changed
- Standard name mapping improvements


## v0.1.0 - 2016-10-23

First useable release.

[v1.1.2]: https://github.com/PHPCSStandards/composer-installer/compare/v1.1.1...v1.1.2
[v1.1.1]: https://github.com/PHPCSStandards/composer-installer/compare/v1.1.0...v1.1.1
[v1.1.0]: https://github.com/PHPCSStandards/composer-installer/compare/v1.0.0...v1.1.0
[v1.0.0]: https://github.com/PHPCSStandards/composer-installer/compare/v0.7.2...v1.0.0
[v0.7.2]: https://github.com/PHPCSStandards/composer-installer/compare/v0.7.1...v0.7.2
[v0.7.1]: https://github.com/PHPCSStandards/composer-installer/compare/v0.7.0...v0.7.1
[v0.7.0]: https://github.com/PHPCSStandards/composer-installer/compare/v0.6.2...v0.7.0
[v0.6.2]: https://github.com/PHPCSStandards/composer-installer/compare/v0.6.1...v0.6.2
[v0.6.1]: https://github.com/PHPCSStandards/composer-installer/compare/v0.6.0...v0.6.1
[v0.6.0]: https://github.com/PHPCSStandards/composer-installer/compare/v0.5.0...v0.6.0
[v0.5.0]: https://github.com/PHPCSStandards/composer-installer/compare/v0.4.4...v0.5.0
[v0.4.4]: https://github.com/PHPCSStandards/composer-installer/compare/v0.4.3...v0.4.4
[v0.4.3]: https://github.com/PHPCSStandards/composer-installer/compare/v0.4.2...v0.4.3
[v0.4.2]: https://github.com/PHPCSStandards/composer-installer/compare/v0.4.1...v0.4.2
[v0.4.1]: https://github.com/PHPCSStandards/composer-installer/compare/v0.4.0...v0.4.1
[v0.4.0]: https://github.com/PHPCSStandards/composer-installer/compare/v0.3.2...v0.4.0
[v0.3.2]: https://github.com/PHPCSStandards/composer-installer/compare/v0.3.1...v0.3.2
[v0.3.1]: https://github.com/PHPCSStandards/composer-installer/compare/v0.3.0...v0.3.1
[v0.3.0]: https://github.com/PHPCSStandards/composer-installer/compare/v0.2.1...v0.3.0
[v0.2.1]: https://github.com/PHPCSStandards/composer-installer/compare/v0.2.0...v0.2.1
[v0.2.0]: https://github.com/PHPCSStandards/composer-installer/compare/v0.1.1...v0.2.0
[v0.1.1]: https://github.com/PHPCSStandards/composer-installer/compare/v0.1.0...v0.1.1

[PHP_CodeSniffer]: https://github.com/PHPCSStandards/PHP_CodeSniffer

[@bastianschwarz]:     https://github.com/bastianschwarz
[@BrianHenryIE]:       https://github.com/BrianHenryIE
[@christopher-hopper]: https://github.com/christopher-hopper
[@fredden]:            https://github.com/fredden
[@frenck]:             https://github.com/frenck
[@gapple]:             https://github.com/gapple
[@GaryJones]:          https://github.com/GaryJones
[@GrahamCampbell]:     https://github.com/GrahamCampbell
[@jrfnl]:              https://github.com/jrfnl
[@kevinfodness]:       https://github.com/kevinfodness
[@mjrider]:            https://github.com/mjrider
[@ntwb]:               https://github.com/ntwb
[@paras-malhotra]:     https://github.com/paras-malhotra
[@Potherca]:           https://github.com/Potherca
[@Seldaek]:            https://github.com/Seldaek
[@SplotyCode]:         https://github.com/SplotyCode
[@TravisCarden]:       https://github.com/TravisCarden
