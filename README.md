# Pressbooks Plugin Scaffold

**Contributors:** conner_bw, greatislander, steelwagstaff, arzola, fdalcin \
**Tags:** pressbooks, plugin, scaffolding \
**Requires at least:** 6.2 \
**Tested up to:** 6.2 \
**Stable tag:** 0.5.0-dev \
**License:** GPLv3 or later \
**License URI:** https://www.gnu.org/licenses/gpl-3.0.html

Scaffolding for a Pressbooks plugin.

## Description

This is not a plugin, but a tool that helps you scaffold a plugin.

## Installation

# Create Plugin

Run `composer create-project pressbooks/pressbooks-plugin-scaffold <your-plugin-slug>`.

Uncomment lines 34-43 of `pressbooks-plugin-scaffold.php` to enable Composer autoloader (you'll need to require a class to test for first).

Replace `pressbooks/pressbooks-plugin-scaffold` with `<your-github-username>/<your-plugin-slug>` throughout the project.

Replace `pressbooks-plugin-scaffold` with `<your-plugin-slug>` throughout the project (renaming files as needed).

Replace `PressbooksPluginScaffold` with `<YourNamespace>` throughout the project.

Run `npm install` to install dependencies.

Update readme.txt to reflect your plugin's name and description and run `composer readme` to generate an updated readme.md file.

# Optional Steps

Configure GitHub Action deploys (instructions to come).

Configure Transifex project and localization (instructions to come).

# Helpful Commands

`composer standards`: check PHP coding standards with Laravel Pint \
`composer fix`: fix PHP coding standards with Laravel Pint \
`composer test`: run unit tests with PHPUnit \
`composer readme`: generate a Markdown readme from readme.txt \
`npm run dev`:  build assets for development \
`npm run build`: build assets for distribution

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

## Changelog

### 0.6.0
#### Upgrade Notice
Pressbooks Plugin Scaffold 0.6.0 requires PHP 8.1, Pressbooks >= 6.16.0, and WordPress >= 6.4
