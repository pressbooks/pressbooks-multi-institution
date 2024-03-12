<?php

namespace PressbooksMultiInstitution\Services;

use PressbooksMultiInstitution\Models\Institution;

use function PressbooksMultiInstitution\Support\get_institution_by_manager;

class NetworkStatsService
{
    private null|Institution $institution;

    private string $columnName = 'Institution';

    public function __construct()
    {
        $institutionId = get_institution_by_manager();
        $this->institution = $institutionId ? Institution::find($institutionId) : null;
    }

    public function setupHooks(): void
    {
        add_filter('pressbooks_network_analytics_stats_title', [$this, 'getStatsTitle']);
        add_filter('pressbooks_network_analytics_stats_download_filename', [$this, 'replaceDownloadsFilename']);
        add_filter('pressbooks_network_analytics_stats_blogmeta_query', [$this, 'addInstitutionToBlogmetaQuery'], 10, 2);
        add_filter('pressbooks_network_analytics_stats_user_query', [$this, 'addInstitutionToUserQuery'], 10, 2);
    }

    public function replaceDownloadsFilename(string $filename): string
    {
        return $this->institution ?
            str_replace('Network', $this->institution->name, $filename)
            : $filename;
    }

    public function getStatsTitle(): string
    {
        return $this->institution ?
            sprintf(__('%s Stats', 'pressbooks-multi-institution'), $this->institution->name)
            : __('Network Stats', 'pressbooks-multi-institution');
    }

    public function addInstitutionToBlogmetaQuery(array $values, string $blogmetaAlias): array
    {
        global $wpdb;
        return [
            'columnAlias' => $this->columnName,
            'column' => "i.name AS {$this->columnName}",
            'join' => " LEFT OUTER JOIN {$wpdb->base_prefix}institutions_blogs AS ib ON ib.blog_id = {$blogmetaAlias}.blog_id LEFT OUTER JOIN {$wpdb->base_prefix}institutions AS i ON i.id = ib.institution_id",
            'conditions' => $this->institution ? "i.id = {$this->institution->id}" : '',
        ];
    }

    public function addInstitutionToUserQuery(array $values, string $userTableAlias): array
    {
        global $wpdb;
        return [
            'columnAlias' => $this->columnName,
            'column' => "i.name AS {$this->columnName}",
            'join' => " LEFT OUTER JOIN {$wpdb->base_prefix}institutions_users AS iu ON iu.user_id = {$userTableAlias}.ID LEFT OUTER JOIN {$wpdb->base_prefix}institutions AS i ON i.id = iu.institution_id",
            'conditions' => $this->institution ? "i.id = {$this->institution->id}" : '',
        ];
    }
}
