<?php

namespace PressbooksMultiInstitution\Interfaces;

interface MigrationInterface
{
    public function up(): void;

    public function down(): void;
}
