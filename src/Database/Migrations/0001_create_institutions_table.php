<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use PressbooksMultiInstitution\Interfaces\MigrationInterface;

return new class implements MigrationInterface {
    public function up(): void
    {
        /** @var Builder $schema */
        $schema = app('db')->schema();

        $schema->create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('book_limit')->nullable();
            $table->unsignedInteger('user_limit')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        /** @var Builder $schema */
        $schema = app('db')->schema();

        $schema->dropIfExists('institutions');
    }
};
