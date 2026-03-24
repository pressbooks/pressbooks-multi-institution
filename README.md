# Pressbooks Shared Network
**Contributors:** arzola, fdalcin \
**Tags:** pressbooks, plugin \
**Requires at least:** 6.4.3 \
**Tested up to:** 6.4.3
<!-- x-release-please-start-version -->
**Stable tag:** 1.8.0
<!-- x-release-please-end -->
**License:** GPLv3 or later
**License URI:** https://www.gnu.org/licenses/gpl-3.0.html

## Description

Tools for managing Pressbooks networks shared by multiple institutions.

## Requirements
* Pressbooks >= 6.16.0
* PHP >= 8.1

## Installation

`composer require pressbooks/pressbooks-multi-institution`

Or download the latest version from the releases page and unzip into your WordPress plugin directory: https://github.com/pressbooks/pressbooks-multi-institution/releases

Then activate and configure the plugin at the Network level in Pressbooks.

## Integrations

### Content Toolkit Integration

This plugin extends the [pressbooks-content-checker](https://github.com/pressbooks/pressbooks-content-checker) plugin to add institution-level filtering and display to the bulk scan reports admin page for network managers.

#### Features

- Adds an **Institution** column to the Book Scan table
- Filters reports so institutional managers only see books from their institution
- Makes the Institution column sortable

#### How It Works

The integration uses filter hooks provided by `pressbooks-content-checker` plugin:

```php
// Add Institution column after Book Title
add_filter('pressbooks_content_checker_book_scan_table_columns', function($columns) {
    // Insert 'institution' column after 'blog_title'
    return $modified_columns;
});

// Make the column sortable
add_filter('pressbooks_content_checker_book_scan_table_sortable_columns', function($columns) {
    $columns['institution'] = ['institution', false];
    return $columns;
});

// Define how to sort by institution in the database
add_filter('pressbooks_content_checker_book_scan_table_orderby_map', function($map) {
    $map['institution'] = [
        'expression' => 'MAX(wp_institutions.name)',
    ];
    return $map;
});

// Filter query to join institutions table and restrict by manager's institution
add_filter('pressbooks_content_checker_book_scan_table_query', function($query) {
    return $query
        ->leftJoin('institutions_blogs', ...)
        ->leftJoin('institutions', ...)
        ->selectRaw('MAX(wp_institutions.name) AS institution')
        ->when($isManager, fn($q) => $q->where('institutions.id', $managersInstitutionId));
});
```

See `src/Integrations/ContentCheckerBooksScanTable.php` for the full implementation.

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

### Integrations

Integration classes for extending other plugins are located in the `src/Integrations` directory.

### Views

Composed Views like WP_List_Table are located in the `src/Views` directory.

Blade templates are located in the `resources/views/{namespace}` directory.

### Models

Models are located in the `src/Models` directory.

### Changelog
Please see the [CHANGELOG](CHANGELOG.md) file for more information.
