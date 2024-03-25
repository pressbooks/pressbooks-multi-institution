<?php

namespace PressbooksMultiInstitution\Views;

use Illuminate\Database\Capsule\Manager;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

abstract class BaseInstitutionList
{
    public function __construct(protected readonly Manager $db)
    {
    }

    public function addFilters(array $filters): array
    {
        if (! is_super_admin()) {
            return $filters;
        }

        if (get_institution_by_manager() > 0) {
            return $filters;
        }

        return [
            ...$filters,
            [
                'field' => 'institution',
                'name' => 'institution[]',
                'counterId' => 'institutions-tab-counter',
            ]
        ];
    }

    public function appendAdditionalColumnsToQuery(): string
    {
        $prefix = $this->db
            ->getDatabaseManager()
            ->getTablePrefix();

        $idSubQuery = $this->db
            ->table('institutions')
            ->select('institutions.id')
            ->join('institutions_blogs', 'institutions.id', '=', 'institutions_blogs.institution_id')
            ->whereRaw("{$prefix}institutions_blogs.blog_id = b.blog_id");

        $nameSubQuery = $this->db
            ->table('institutions')
            ->select('institutions.name')
            ->join('institutions_blogs', 'institutions.id', '=', 'institutions_blogs.institution_id')
            ->whereRaw("{$prefix}institutions_blogs.blog_id = b.blog_id");

        return "({$idSubQuery->toSql()}) as institution_id, ({$nameSubQuery->toSql()}) as institution";
    }

    public function appendAdditionalWhereClausesToQuery(string $where): string
    {
        global $wpdb;

        $institutionId = get_institution_by_manager();

        if (is_super_admin() && $institutionId > 0) {
            return "{$where} AND (institution_id = $institutionId)";
        }

        $institutionIds = array_map(fn (string $value) => (int) $value, $_GET['institution'] ?? []);

        if (! $institutionIds) {
            return $where;
        }

        $ids = array_filter($institutionIds, fn (int $value) => $value > 0);

        $unassigned = count($institutionIds) > count($ids);

        $whereIn = null;
        $whereNull = null;

        if ($ids) {
            $placeholder = implode(', ', array_fill(0, count($ids), '%d'));

            $whereIn = $wpdb->prepare("institution_id IN ({$placeholder})", $ids);
        }

        if ($unassigned) {
            $whereNull = 'institution_id IS NULL';
        }

        if ($whereIn && $whereNull) {
            return "{$where} AND ({$whereIn} OR {$whereNull})";
        }

        if ($whereIn) {
            return "{$where} AND ({$whereIn})";
        }

        if ($whereNull) {
            return "{$where} AND ({$whereNull})";
        }

        return $where;
    }
    abstract public function addColumns(array $columns): array;
    abstract public function getCustomTexts(array $texts): array;
}
