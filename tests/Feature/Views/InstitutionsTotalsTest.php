<?php

namespace Tests\Feature\Views;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionUser;
use PressbooksMultiInstitution\Views\InstitutionsTotals;
use Tests\TestCase;
use Tests\Traits\CreatesModels;

/**
 * @group institutions-totals
 */
class InstitutionsTotalsTest extends TestCase
{
    use CreatesModels;

    /**
     * @test
     */
    public function it_returns_totals_without_users_unassigned(): void
    {
        $users = get_users(['fields' => ['ID']]);

        $this->createInstitutionsUsers(3, 10);

        InstitutionUser::query()->create([
            'institution_id' => Institution::query()->first()->id,
            'user_id' => $users[0]->ID,
        ]);

        update_option('pb_plan_settings_book_limit', 10);

        $expected = [
            [
                'type' => 'Unassigned',
                'book_total' => 0,
                'user_total' => 0,
            ],
            [
                'type' => 'Shared Network Totals',
                'book_total' => '0/10',
                'user_total' => 11,
            ],
            [
                'type' => 'Premium Member Totals',
                'book_total' => 0,
                'user_total' => 0,
            ],
            [
                'type' => 'All Network totals',
                'book_total' => 0,
                'user_total' => 11,
            ],
        ];

        $this->assertEquals($expected, (new InstitutionsTotals(app('db')))->getTotals());
    }

    /**
     * @test
     */
    public function it_returns_totals(): void
    {
        $this->createInstitutionsUsers(3, 10);

        $institution = Institution::query()->first();
        $institution->update(['buy_in' => true]);

        $premiumUsers = $institution->users()->count();

        $this->newUser();

        $this->newBook();

        update_option('pb_plan_settings_book_limit', 5);

        $expected = [
            [
                'type' => 'Unassigned',
                'book_total' => 1,
                'user_total' => 2,
            ],
            [
                'type' => 'Shared Network Totals',
                'book_total' => '1/5',
                'user_total' => 12 - $premiumUsers,
            ],
            [
                'type' => 'Premium Member Totals',
                'book_total' => 0,
                'user_total' => $premiumUsers,
            ],
            [
                'type' => 'All Network totals',
                'book_total' => 1,
                'user_total' => 12,
            ],
        ];

        $this->assertEquals($expected, (new InstitutionsTotals(app('db')))->getTotals());
    }
}
