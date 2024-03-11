<?php

namespace PressbooksMultiInstitution\Services;

use PressbooksMultiInstitution\Models\Institution;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class NetworkStatsService
{
    private Institution $institution;

    public function __construct()
    {
        $institutionId = get_institution_by_manager();
        if ($institutionId === 0) {
            return;
        }

        $this->institution = Institution::find($institutionId);
    }

    public function setupHooks(): void
    {
        if (get_institution_by_manager() !== 0) {
            add_filter('pb_network_analytics_stats_title', [$this, 'getStatsTitle']);
            add_filter('pressbooks_network_analytics_usersovertime_query_conditions', [$this, 'addUsersOverTimeConditions']);
            add_filter('pb_network_analytics_stats_download_filename', [$this, 'replaceDownloadsFilename']);
            add_filter('pressbooks_network_analytics_stats_blogmeta_conditions', [$this, 'addBlogMetaCondition'], 10, 2);
        }
        add_filter('pb_network_analytics_downloads_additional_columns', [$this, 'getInstitutionColumn'], 10, 3);
    }

    public function replaceDownloadsFilename(string $filename): string
    {
        return str_replace('Network', $this->institution->name, $filename);
    }

    public function getStatsTitle(): string
    {
        return sprintf(__('%s Stats', 'pressbooks-multi-institution'), $this->institution->name);
    }

    public function getInstitutionColumn(string $column, string $blogmetaAlias, bool $subQuery = true): string
    {
        $columnName = 'Institution';

        global $wpdb;
        return $subQuery ? <<<SQL
(SELECT name
	FROM {$wpdb->base_prefix}institutions AS i
	LEFT OUTER JOIN {$wpdb->base_prefix}institutions_blogs AS ib
	ON i.id = ib.institution_id
	WHERE ib.blog_id = {$blogmetaAlias}.blog_id) AS {$columnName}
SQL : $columnName;
    }

    public function addBlogMetaCondition(string $condition, string $blogmetaAlias): string
    {
        $blogIds = $this->institution->books()->pluck('blog_id')->join(', ');
        if (! $blogIds) {
            return '';
        }

        return "{$blogmetaAlias}.blog_id IN ({$blogIds})";
    }

    public function addUsersOverTimeConditions(): string
    {
        $userIds = $this->institution->users()->pluck('user_id')->join(', ');

        return "ID IN ({$userIds})";
    }
}
