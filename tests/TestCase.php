<?php

namespace Tests;

use Illuminate\Database\Capsule\Manager;
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

        /** @var Manager $db */
        $db = app('db');

        $db->table('users')->where('user_login', '<>', 'admin')->delete();
        $db->table('blogs')->where('blog_id', '<>', get_main_site_id())->delete();

        Migration::rollback();
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        (new Bootstrap)->setUp();
    }
}
