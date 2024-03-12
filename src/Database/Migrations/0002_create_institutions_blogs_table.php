<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use PressbooksMultiInstitution\Interfaces\MigrationInterface;

return new class implements MigrationInterface {
    public function up(): void
    {
        /** @var Builder $schema */
        $schema = app('db')->schema();

        $schema->create('institutions_blogs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('blog_id');
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
