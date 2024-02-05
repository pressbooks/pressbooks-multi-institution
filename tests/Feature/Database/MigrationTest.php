<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Builder;
use PressbooksMultiInstitution\Database\Migration;
use WP_UnitTestCase;

class MigrationTest extends WP_UnitTestCase
{
    public function setUp(): void
    {
        // Make sure tables are not created
        Migration::rollback();

        parent::setUp();
    }

    /**
     * @test
     */
    public function it_creates_the_institutions_table(): void
    {
        $this->assertTableMissing('institutions');

        Migration::migrate();

        $this->assertTableExists('institutions', [
            'id',
            'name',
            'book_limit',
            'user_limit',
            'created_at',
            'updated_at',
        ]);

        Migration::rollback();
    }

    /**
     * @test
     */
    public function it_creates_the_institutions_blogs_table(): void
    {
        $this->assertTableMissing('institutions_blogs');

        Migration::migrate();

        $this->assertTableExists('institutions_blogs', [
            'id',
            'blog_id',
            'institution_id',
        ]);

        Migration::rollback();
    }

    /**
     * @test
     */
    public function it_creates_the_institutions_users_table(): void
    {
        $this->assertTableMissing('institutions_users');

        Migration::migrate();

        $this->assertTableExists('institutions_users', [
            'id',
            'user_id',
            'institution_id',
            'manager',
        ]);

        Migration::rollback();
    }

    /**
     * @test
     */
    public function it_creates_the_institutions_email_domains_table(): void
    {
        $this->assertTableMissing('institutions_email_domains');

        Migration::migrate();

        $this->assertTableExists('institutions_email_domains', [
            'id',
            'institution_id',
            'domain',
            'created_at',
            'updated_at',
        ]);

        Migration::rollback();
    }

    /**
     * @test
     */
    public function it_drops_tables_upon_rollback(): void
    {
        Migration::migrate();

        $this->assertTableExists('institutions');
        $this->assertTableExists('institutions_blogs');
        $this->assertTableExists('institutions_users');
        $this->assertTableExists('institutions_email_domains');

        Migration::rollback();

        $this->assertTableMissing('institutions');
        $this->assertTableMissing('institutions_blogs');
        $this->assertTableMissing('institutions_users');
        $this->assertTableMissing('institutions_email_domains');
    }

    protected function assertTableMissing(string $table): void
    {
        /** @var Builder $schema */
        $schema = app('db')->schema();

        $this->assertFalse($schema->hasTable($table));
    }

    protected function assertTableExists(string $table, ?array $columns = null): void
    {
        /** @var Builder $schema */
        $schema = app('db')->schema();

        $this->assertTrue($schema->hasTable($table));

        if ($columns) {
            $this->assertEquals($columns, $schema->getColumnListing($table));
        }
    }
}
