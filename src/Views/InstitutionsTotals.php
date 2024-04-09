<?php

namespace PressbooksMultiInstitution\Views;

use Illuminate\Database\Capsule\Manager;

use function PressbooksMultiInstitution\Support\is_network_unlimited;

class InstitutionsTotals
{
    private Manager $db;
    private string $prefix;

    public function __construct()
    {
        $this->db = app('db');
        $this->prefix = $this->db->getDatabaseManager()->getTablePrefix();
    }

    public function getTotals(): array
    {
        $bookCounts = $this->getBookCounts();

        $userCounts = $this->getUserCounts();

        $rows[] = [
            'type' => __('Unassigned', 'pressbooks-multi-institution'),
            'book_total' => $bookCounts->unassigned,
            'user_total' => $userCounts->unassigned,
        ];

        $sharedBookCount = $bookCounts->unassigned + $bookCounts->shared;
        $sharedUserCount = $userCounts->unassigned + $userCounts->shared;

        $networkLimit = get_option('pb_plan_settings_book_limit', null);
        $unlimitedNetwork = is_network_unlimited();

        if ($unlimitedNetwork) {
            $sharedBookCount = $bookCounts->total . '/' . __('unlimited', 'pressbooks-multi-institution');
            $sharedUserCount = $userCounts->total;
        } else {
            $sharedBookCount .= $networkLimit ? '/' . $networkLimit : '';
        }

        $rows[] = [
            'type' => __('Shared Network Totals', 'pressbooks-multi-institution'),
            'book_total' => $sharedBookCount,
            'user_total' => $sharedUserCount,
        ];

        if (! $unlimitedNetwork) {
            $rows[] = [
                'type' => __('Premium Member Totals', 'pressbooks-multi-institution'),
                'book_total' => $bookCounts->premium,
                'user_total' => $userCounts->premium,
            ];
        }

        $rows[] = [
            'type' => __('All Network totals', 'pressbooks-multi-institution'),
            'book_total' => $bookCounts->total,
            'user_total' => $userCounts->total,
        ];

        return $rows;
    }

    private function getUserCounts(): object
    {
        return $this->db->table('users')
            ->selectRaw("count(*) as total")
            ->selectRaw("count(case when {$this->prefix}institutions.id is null then 1 end) as unassigned")
            ->selectRaw("count(case when {$this->prefix}institutions.id is not null and {$this->prefix}institutions.buy_in = false then 1 end) as shared")
            ->selectRaw("count(case when {$this->prefix}institutions.id is not null then 1 end) as assigned")
            ->selectRaw("count(case when {$this->prefix}institutions.id is not null and {$this->prefix}institutions.buy_in = true then 1 end) as premium")
            ->leftJoin('institutions_users', 'institutions_users.user_id', '=', 'users.ID')
            ->leftJoin('institutions', 'institutions.id', '=', 'institutions_users.institution_id')
            ->first();
    }

    private function getBookCounts(): object
    {
        return $this->db->table('blogs')
            ->selectRaw("count(*) as total")
            ->selectRaw("count(case when {$this->prefix}institutions.id is null then 1 end) as unassigned")
            ->selectRaw("count(case when {$this->prefix}institutions.id is not null and {$this->prefix}institutions.buy_in = false then 1 end) as shared")
            ->selectRaw("count(case when {$this->prefix}institutions.id is not null then 1 end) as assigned")
            ->selectRaw("count(case when {$this->prefix}institutions.id is not null and {$this->prefix}institutions.buy_in = true then 1 end) as premium")
            ->leftJoin('institutions_blogs', 'institutions_blogs.blog_id', '=', 'blogs.blog_id')
            ->leftJoin('institutions', 'institutions.id', '=', 'institutions_blogs.institution_id')
            ->where('blogs.blog_id', '<>', get_main_site_id())
            ->first();
    }
}
