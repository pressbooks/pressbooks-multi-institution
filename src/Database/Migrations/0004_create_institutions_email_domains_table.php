<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use PressbooksMultiInstitution\Interfaces\MigrationInterface;

return new class implements MigrationInterface {
    public function up(): void
    {
        /** @var Builder $schema */
        $schema = app('db')->schema();

        if ($schema->hasTable('institutions_email_domains')) {
            return;
        }

        $schema->create('institutions_email_domains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institution_id');
            $table->string('domain');
            $table->timestamps();

            $table->foreign('institution_id')
                ->references('id')
                ->on('institutions')
                ->cascadeOnDelete();

            $table->unique(['institution_id', 'domain']);
        });
    }

    public function down(): void
    {
        /** @var Builder $schema */
        $schema = app('db')->schema();

        $schema->dropIfExists('institutions_email_domains');
    }
};
