<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use PressbooksMultiInstitution\Interfaces\MigrationInterface;

return new class implements MigrationInterface {
    public function up(): void
    {
        /** @var Builder $schema */
        $schema = app('db')->schema();

        if ($schema->hasTable('institutions_blogs')) {
            return;
        }

        $connection = app('db')->connection();
        $prefix = $connection->getTablePrefix();
        $blogsColumn = $connection->selectOne(
            "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'blog_id'",
            [$prefix . 'blogs']
        );
        $blogIdIsUnsigned = $blogsColumn && str_contains(strtolower($blogsColumn->COLUMN_TYPE), 'unsigned');

        $schema->create('institutions_blogs', function (Blueprint $table) use ($blogIdIsUnsigned) {
            $table->id();
            if ($blogIdIsUnsigned) {
                $table->unsignedBigInteger('blog_id');
            } else {
                $table->bigInteger('blog_id');
            }
            $table->unsignedBigInteger('institution_id');

            $table->foreign('blog_id')
                ->references('blog_id')
                ->on('blogs')
                ->cascadeOnDelete();
            $table->foreign('institution_id')
                ->references('id')
                ->on('institutions')
                ->cascadeOnDelete();

            $table->unique(['blog_id']);
        });
    }

    public function down(): void
    {
        /** @var Builder $schema */
        $schema = app('db')->schema();

        $schema->dropIfExists('institutions_blogs');
    }
};
