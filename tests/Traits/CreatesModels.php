<?php

namespace Tests\Traits;

use PressbooksMultiInstitution\Models\Institution;
use PressbooksMultiInstitution\Models\InstitutionUser;

trait CreatesModels
{
    protected function newUser(array $properties = []): int
    {
        global $wpdb;

        $wpdb->query('START TRANSACTION');

        $properties = [
            'first_name' => $properties['first_name'] ?? 'John',
            'last_name' => $properties['last_name'] ?? 'Doe',
            'user_login' => $properties['user_login'] ?? 'johndoe',
            'user_email' => $properties['user_email'] ?? 'johndoe@fakedomain.edu',
        ];

        $wpdb->delete($wpdb->users, [
            'user_login' => $properties['user_login'],
        ]);

        $user = $this->factory()->user->create($properties);

        $wpdb->query('COMMIT');

        return $user;
    }

    protected function newBook(array $properties = []): int
    {
        global $wpdb;

        $wpdb->query('START TRANSACTION');

        add_filter('pb_redirect_to_new_book', '__return_false');

        $properties = [
            'path' => $properties['path'] ?? 'fakebook',
        ];

        $wpdb->delete($wpdb->blogs, [
            'path' => "/{$properties['path']}/",
        ]);

        $blog = $this->factory()->blog->create($properties);

        $wpdb->query('COMMIT');

        switch_to_blog($blog);

        return $blog;
    }

    public function createInstitution(array $properties): Institution
    {
        return Institution::create([
            'name' => $properties['name'] ?? 'Fake Institution',
            'book_limit' => $properties['book_limit'] ?? 10,
            'user_limit' => $properties['user_limit'] ?? 10,
        ]);
    }

    public function createInstitutionsUsers(int $institutionsLimit, int $usersLimit): void
    {
        $institutions = [];
        for ($i = 0; $i < $institutionsLimit; $i++) {
            $institutions[] = $this->createInstitution(['name' => "Institution {$i}"]);
        }

        for ($i = 0; $i < $usersLimit; $i++) {
            $user_id = $this->newUser([
                'user_login' => "johndoe{$i}",
                'user_email' => "j{$i}@fake.test",
                'first_name' => "John{$i}",
                'last_name' => "Doe{$i}",
            ]);

            InstitutionUser::query()->create([
                'user_id' => $user_id,
                'institution_id' => $institutions[array_rand($institutions)]->id,
            ]);
        }
    }
}