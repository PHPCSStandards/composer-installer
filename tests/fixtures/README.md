# Test fixtures

The subdirectories in this folder contain "fake" PHPCS standards.

As more and more external PHPCS standards include a `require` for this plugin, these test fixtures should be used in the integration tests instead of _real_ PHPCS standards.

Using these fixtures will make creating tests more straight-forward as:
* It should prevent issues with the plugin version as used in the tests (version of the created zip artifact) not matching the version constraint for the plugin in a _real_ standard, which would result in Composer downloading from Packagist instead of using the artifact package created for use in the tests.
* It means we don't need to keep track of the version history of external standards in regards to:
    - whether or not the external standard uses the correct project `type` in their `composer.json`.
    - whether or not they `require` the plugin.
    - whether or not they comply with the PHPCS naming conventions.
    - whether or not the standard is compatible with PHPCS 2.x/3.x/4.x.
    - etc...

Each subdirectory in this `fixtures` directory will be zipped up and placed in the `artifact` subdirectory ahead of running the tests, making them available to all tests.

The artifact version of each fake standard will always be `1.0.0`, unless otherwise indicated.
Setting a different version for a fake standard can be achieved by explicitly setting the `version` in the `composer.json` file of the fake standard.

Any particular test can use one or more of these fake standards.

Notes:
* The "fake" standards DO need to comply with the naming conventions from PHPCS, which means that the name of a standard as set in the `ruleset.xml` file MUST be the same as the name of the directory containing the `ruleset.xml` file.
    So the `ruleset.xml` file for a standard called `Dummy` MUST be in a (sub)directory named `Dummy`.
* A "fake" standard will normally consist of a `composer.json` file in the fake project root and one or more `ruleset.xml` files.
* If the "fake" standard `require`s the plugin, it should do so with `'*'` as the version constraint.
* The "fake" standards generally do NOT need to contain any sniffs or actual rules in the ruleset (unless the standard will be used in a test for running PHPCS).

It is recommended to add a short description of the situation which can be tested with each fixture to the below list.

## Valid packages

### Package name: `phpcs-composer-installer/dummy-subdir`

**Description:**
An external PHPCS standard with the `ruleset.xml` file in a subdirectory ("normal" standard setup).

| Characteristics          | Notes                                                                                            |
|--------------------------|--------------------------------------------------------------------------------------------------|
| **Standard(s):**         | `DummySubDir`                                                                                    |
| **Includes sniff(s):**   | :heavy_checkmark: One sniff - `DummySubDir.Demo.Demo` - which is PHPCS cross-version compatible. |
| **Requires the plugin:** | :x:                                                                                              |

### Package name: `phpcs-composer-installer/multistandard`

**Description:**
An external PHPCS standard with multiple rulesets, each in a subdirectory ("normal" standard setup).

| Characteristics          | Notes                                                      |
|--------------------------|------------------------------------------------------------|
| **Standard(s):**         | `MyFirstStandard`, `MySecondStandard`, `My-Third-Standard` |
| **Includes sniff(s):**   | :x:                                                        |
| **Requires the plugin:** | :heavy_checkmark:                                          |

### Package name: `phpcs-composer-installer/dummy-src`

**Description:**
An external PHPCS standard with the `ruleset.xml` file in a deeper nested subdirectory.

| Characteristics          | Notes             |
|--------------------------|-------------------|
| **Standard(s):**         | `DummySrcSubDir`  |
| **Includes sniff(s):**   | :x:               |
| **Requires the plugin:** | :heavy_checkmark: |


## Invalid packages

**_These packages should not be installed by the plugin._**

### Package name: `phpcs-composer-installer/no-ruleset`

**Description:**
A Composer package which has the `phpcodesniffer-standard` type set in the `composer.json` file, but doesn't contain a `ruleset.xml` file (but does contain a `ruleset.xml.dist` file, which is not a file recognized by PHPCS).

| Characteristics          | Notes             |
|--------------------------|-------------------|
| **Standard(s):**         | `NoRuleset`       |
| **Requires the plugin:** | :heavy_checkmark: |

### Package name: `phpcs-composer-installer/incorrect-type`

**Description:**
An external PHPCS standard which does not have the `phpcodesniffer-standard` type set in the `composer.json` file.

| Characteristics          | Notes             |
|--------------------------|-------------------|
| **Standard(s):**         | `IncorrectType`   |
| **Requires the plugin:** | :heavy_checkmark: |
