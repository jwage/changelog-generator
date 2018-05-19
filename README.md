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

## TODO

- Connect related issues and pull requests to avoid duplication.
- Allow filtering of labels.
- Allow configuration PHP file that supports 1 or many projects.
- Allow changelog to be written to a file with a command option.
