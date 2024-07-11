# Pressbooks Shared Network
**Contributors:** arzola, fdalcin \
**Tags:** pressbooks, plugin \
**Requires at least:** 6.4.3 \
**Tested up to:** 6.4.3 \
<!-- x-release-please-start-version -->
**Stable tag:** 1.0.2 \
<!-- x-release-please-end -->
**License:** GPLv3 or later \
**License URI:** https://www.gnu.org/licenses/gpl-3.0.html

## Description

Tools for managing Pressbooks networks shared by multiple institutions 

## Requirements
* Pressbooks >= 6.16.0
* PHP >= 8.1

## Installation

`composer require pressbooks/pressbooks-multi-institution`

Or download the latest version from the releases page and unzip into your WordPress plugin directory: https://github.com/pressbooks/pressbooks-multi-institution/releases

Then activate and configure the plugin at the Network level in Pressbooks.

## Helpful Commands

`composer standards`: check PHP coding standards with Laravel Pint \
`composer fix`: fix PHP coding standards with Laravel Pint \
`composer test`: run unit tests with PHPUnit \
`composer readme`: generate a Markdown readme from readme.txt \
`npm run dev`:  build assets for development \
`npm run build`: build assets for distribution \
`wp pb:reset-db-schema`: deletes the plugin's data and reset the database schema

## Directory Structure

### Controllers

Controllers are responsible for handling requests and returning responses. They are located in the `src/Controllers` directory.

### Database

Database migrations are located in the `src/Database/Migrations` directory.

### Views

Composed Views like WP_List_Table are located in the `src/Views` directory.

Blade templates are located in the `resources/views/{namespace}` directory.

### Models

Models are located in the `src/Models` directory.

### Changelog
Please see the [CHANGELOG](CHANGELOG.md) file for more information.
