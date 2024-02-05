<?php

namespace Tests;

use PressbooksMultiInstitution\Bootstrap;
use PressbooksMultiInstitution\Database\Migration;
use WP_UnitTestCase;

class TestCase extends WP_UnitTestCase
{
    public function setUp(): void
    {
        Migration::migrate();

        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Migration::rollback();
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        (new Bootstrap)->setUp();
    }
}
