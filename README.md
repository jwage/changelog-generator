# Changelog Generator

[![Build Status](https://travis-ci.org/jwage/changelog-generator.svg)](https://travis-ci.org/jwage/changelog-generator)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jwage/changelog-generator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jwage/changelog-generator/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jwage/changelog-generator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jwage/changelog-generator/?branch=master)

This library will generate a changelog markdown document from a GitHub milestone. It is based off of
[weierophinney/changelog_generator](https://github.com/weierophinney/changelog_generator).

## Installation

You can install with composer:

    $ composer require jwage/changelog-generator

## Usage

Generate a change log based on a GitHub milestone with the following command:

    $ ./vendor/bin/changelog-generator generate --user=doctrine --repository=migrations --milestone=2.0

### Write to File

Write the generated changelog to a file with the `--file` option. If you don't provide a value, it will be written
to the current working directory in a file named `CHANGELOG.md`:

    $ ./vendor/bin/changelog-generator generate --user=doctrine --repository=migrations --milestone=2.0 --file

You can pass a value to `--file` to specify where the changelog file should be written:

    $ ./vendor/bin/changelog-generator generate --user=doctrine --repository=migrations --milestone=2.0 --file=changelog.md

By default it will overwrite the file contents but you can pass the `--append` option to append the changelog to
the existing contents.

    $ ./vendor/bin/changelog-generator generate --user=doctrine --repository=migrations --milestone=2.0 --file=changelog.md --append

### Connecting Issues & Pull Requests

To make the changelog easier to read, we try to connect pull requests to issues by looking for `#{ISSUE_NUMBER}` in the body
of the pull request. When the user of the issue and pull request are different github users, the changelog line will look like the following:

- [634: Fix namespace in bin/doctrine-migrations.php](https://github.com/doctrine/migrations/pull/634) thanks to @Majkl578 and @jwage
