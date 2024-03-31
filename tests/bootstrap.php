<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once "{$tests_dir}/includes/functions.php";

tests_add_filter('muplugins_loaded', function () {
    require_once(__DIR__ . '/../../pressbooks/pressbooks.php');
    require_once(__DIR__ . '/../../pressbooks/tests/utils-trait.php');
});

require_once "{$tests_dir}/includes/bootstrap.php";
