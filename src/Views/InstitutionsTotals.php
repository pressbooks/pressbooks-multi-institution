<?php

namespace PressbooksMultiInstitution\Views;

use Illuminate\Database\Capsule\Manager;

use function PressbooksMultiInstitution\Support\is_network_unlimited;

class InstitutionsTotals
{
    private string $prefix;

    private const UNASSIGNED_ALIAS = 'unassigned';

    private const SHARED_ALIAS = 'shared';

    private const PREIMIUM_ALIAS = 'premium';

    private const TOTAL_ALIAS = 'total';

    public function __construct(private readonly Manager $db)
    {
        $this->prefix = $this->db->getDatabaseManager()->getTablePrefix();
    }

    public function getTotals(): array
    {
        $bookCounts = $this->getBookCounts();

        $userCounts = $this->getUserCounts();

        $rows[] = [
            'type' => __('Unassigned', 'pressbooks-multi-institution'),
            'book_total' => $bookCounts->{$this::UNASSIGNED_ALIAS},
            'user_total' => $userCounts->{$this::UNASSIGNED_ALIAS},
        ];

        $sharedBookCount = $bookCounts->{$this::UNASSIGNED_ALIAS} + $bookCounts->{$this::SHARED_ALIAS};
        $sharedUserCount = $userCounts->{$this::UNASSIGNED_ALIAS} + $userCounts->{$this::SHARED_ALIAS};

        $networkLimit = get_option('pb_plan_settings_book_limit', null);
        $unlimitedNetwork = is_network_unlimited();

        if ($unlimitedNetwork) {
            $sharedBookCount = $bookCounts->{$this::TOTAL_ALIAS} . '/' . __('unlimited', 'pressbooks-multi-institution');
            $sharedUserCount = $userCounts->{$this::TOTAL_ALIAS};
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
                'book_total' => $bookCounts->{$this::PREIMIUM_ALIAS},
                'user_total' => $userCounts->{$this::PREIMIUM_ALIAS},
            ];
        }

        $rows[] = [
            'type' => __('All Network totals', 'pressbooks-multi-institution'),
            'book_total' => $bookCounts->{$this::TOTAL_ALIAS},
            'user_total' => $userCounts->{$this::TOTAL_ALIAS},
        ];

        return $rows;
    }

    private function getUserCounts(): object
    {
        return $this->db->table('users')
            ->selectRaw("count(*) as " . $this::TOTAL_ALIAS)
            ->selectRaw("count(case when {$this->prefix}institutions.id is null then 1 end) as " . $this::UNASSIGNED_ALIAS)
            ->selectRaw("count(case when {$this->prefix}institutions.id is not null and {$this->prefix}institutions.buy_in = false then 1 end) as " . $this::SHARED_ALIAS)
            ->selectRaw("count(case when {$this->prefix}institutions.id is not null and {$this->prefix}institutions.buy_in = true then 1 end) as " . $this::PREIMIUM_ALIAS)
            ->leftJoin('institutions_users', 'institutions_users.user_id', '=', 'users.ID')
            ->leftJoin('institutions', 'institutions.id', '=', 'institutions_users.institution_id')
            ->first();
    }

    private function getBookCounts(): object
    {
        return $this->db->table('blogs')
            ->selectRaw("count(*) as " . $this::TOTAL_ALIAS)
            ->selectRaw("count(case when {$this->prefix}institutions.id is null then 1 end) as " . $this::UNASSIGNED_ALIAS)
            ->selectRaw("count(case when {$this->prefix}institutions.id is not null and {$this->prefix}institutions.buy_in = false then 1 end) as " . $this::SHARED_ALIAS)
            ->selectRaw("count(case when {$this->prefix}institutions.id is not null and {$this->prefix}institutions.buy_in = true then 1 end) as " . $this::PREIMIUM_ALIAS)
            ->leftJoin('institutions_blogs', 'institutions_blogs.blog_id', '=', 'blogs.blog_id')
            ->leftJoin('institutions', 'institutions.id', '=', 'institutions_blogs.institution_id')
            ->where('blogs.blog_id', '<>', get_main_site_id())
            ->first();
    }
}
