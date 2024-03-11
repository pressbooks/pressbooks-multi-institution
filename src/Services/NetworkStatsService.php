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
            add_filter('pb_network_analytics_stats_download_conditions', [$this, 'addDownloadConditions']);
            add_filter('pb_network_analytics_stats_title', [$this, 'getStatsTitle']);
        }
        add_filter('pb_network_analytics_stats_download_additional_columns', [$this, 'addDownloadSubQuery']);
    }

    public function getStatsTitle(): string
    {
        return sprintf(__('%s Stats', 'pressbooks-multi-institution'), $this->institution->name);
    }

    public function addDownloadSubQuery(): string
    {
        global $wpdb;
        return <<<SQL
(SELECT name
	FROM {$wpdb->base_prefix}institutions AS i
	LEFT OUTER JOIN {$wpdb->base_prefix}institutions_blogs AS ib
	ON i.id = ib.institution_id
	WHERE ib.blog_id = blogmeta.blog_id) AS Institution
SQL;
    }

    public function addDownloadConditions(): string
    {
        $blogIds = $this->institution->books()->pluck('blog_id')->join(', ');
        if (! $blogIds) {
            // institution without any book
            return 'blogmeta.blog_id = -1';
        }

        return "blogmeta.blog_id IN ({$blogIds})";
    }
}
