<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use PressbooksMultiInstitution\Interfaces\MigrationInterface;

return new class implements MigrationInterface {
    public function up(): void
    {
        /** @var Builder $schema */
        $schema = app('db')->schema();

        if (! $schema->hasTable('institutions_users')) {
            $schema->create('institutions_users', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('institution_id');
                $table->boolean('manager')->default(false);

                $table->foreign('user_id')
                    ->references('ID')
                    ->on('users')
                    ->cascadeOnDelete();
                $table->foreign('institution_id')
                    ->references('id')
                    ->on('institutions')
                    ->cascadeOnDelete();

                $table->unique(['user_id']);
            });
        }
    }

    public function down(): void
    {
        /** @var Builder $schema */
        $schema = app('db')->schema();

        $schema->dropIfExists('institutions_users');
    }
};
