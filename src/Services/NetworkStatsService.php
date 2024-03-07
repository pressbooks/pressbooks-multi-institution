<?php

namespace PressbooksMultiInstitution\Services;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionUser;
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
//		dd($this->institution->books()->pluck('blog_id'));
	}

	public function setupHooks(): void
	{
		if (get_institution_by_manager() === 0) {
			return;
		}

		add_filter('pb_network_analytics_stats_title', [$this, 'getStatsTitle']);
		add_filter('pb_network_analytics_stats_download', [$this, 'addDownloadConditions']);
	}

	public function getStatsTitle(): string
	{
		return sprintf(__('%s Stats', 'pressbooks-multi-institution'), $this->institution->name);
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
