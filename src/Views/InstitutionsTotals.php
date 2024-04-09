<?php

namespace PressbooksMultiInstitution\Views;

use function PressbooksMultiInstitution\Support\is_network_unlimited;

class InstitutionsTotals
{
    public function getTotals(): array
    {
        $bookCounts = $this->getBookCounts();

        $userCounts = $this->getUserCounts();

        $rows[] = [
            'name' => __('Unassigned', 'pressbooks-multi-institution'),
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
            'name' => __('Shared Network Totals', 'pressbooks-multi-institution'),
            'book_total' => $sharedBookCount,
            'user_total' => $sharedUserCount,
        ];

        if (! $unlimitedNetwork) {
            $rows[] = [
                'name' => __('Premium Member Totals', 'pressbooks-multi-institution'),
                'book_total' => $bookCounts->premium,
                'user_total' => $userCounts->premium,
            ];
        }

        $rows[] = [
            'name' => __('All Network totals', 'pressbooks-multi-institution'),
            'book_total' => $bookCounts->total,
            'user_total' => $userCounts->total,
        ];

        return $rows;
    }

    private function getUserCounts(): object
    {
        /** @var Manager $db */
        $db = app('db');

        $prefix = $db->getDatabaseManager()->getTablePrefix();

        return $db->table('users')
            ->selectRaw("count(*) as total")
            ->selectRaw("count(case when {$prefix}institutions.id is null then 1 end) as unassigned")
            ->selectRaw("count(case when {$prefix}institutions.id is not null and {$prefix}institutions.buy_in = false then 1 end) as shared")
            ->selectRaw("count(case when {$prefix}institutions.id is not null then 1 end) as assigned")
            ->selectRaw("count(case when {$prefix}institutions.id is not null and {$prefix}institutions.buy_in = true then 1 end) as premium")
            ->leftJoin('institutions_users', 'institutions_users.user_id', '=', 'users.ID')
            ->leftJoin('institutions', 'institutions.id', '=', 'institutions_users.institution_id')
            ->first();
    }

    private function getBookCounts(): object
    {
        /** @var Manager $db */
        $db = app('db');

        $prefix = $db->getDatabaseManager()->getTablePrefix();

        return $db->table('blogs')
            ->selectRaw("count(*) as total")
            ->selectRaw("count(case when {$prefix}institutions.id is null then 1 end) as unassigned")
            ->selectRaw("count(case when {$prefix}institutions.id is not null and {$prefix}institutions.buy_in = false then 1 end) as shared")
            ->selectRaw("count(case when {$prefix}institutions.id is not null then 1 end) as assigned")
            ->selectRaw("count(case when {$prefix}institutions.id is not null and {$prefix}institutions.buy_in = true then 1 end) as premium")
            ->leftJoin('institutions_blogs', 'institutions_blogs.blog_id', '=', 'blogs.blog_id')
            ->leftJoin('institutions', 'institutions.id', '=', 'institutions_blogs.institution_id')
            ->where('blogs.blog_id', '<>', get_main_site_id())
            ->first();
    }
}
