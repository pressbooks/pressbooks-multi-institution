<?php

namespace Tests;

use PressbooksMultiInstitution\Bootstrap;
use PressbooksMultiInstitution\Database\Migration;
use WP_UnitTestCase;

class TestCase extends WP_UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Migration::migrate();

        (new Bootstrap)->setUp();
    }
}
