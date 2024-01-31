<?php

namespace PressbooksMultiInstitution\Database;

use FilesystemIterator;
use Illuminate\Support\Collection;
use PressbooksMultiInstitution\Interfaces\MigrationInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class Migration
{
    public static function migrate(): bool
    {
        (new static)
            ->getMigrationFiles()
            ->sortKeys()
            ->each(fn (MigrationInterface $class) => $class->up());

        return true;
    }

    public static function rollback(): bool
    {
        (new static)
            ->getMigrationFiles()
            ->sortKeysDesc()
            ->each(fn (MigrationInterface $class) => $class->down());

        return true;
    }

    private function getMigrationFiles(): Collection
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                directory: __DIR__ . '/Migrations',
                flags: FilesystemIterator::SKIP_DOTS
            )
        );

        return Collection::make($iterator)
            ->filter(fn (SplFileInfo $record) => $record->isFile())
            ->map(fn (SplFileInfo $record) => require $record->getPathname());
    }
}
