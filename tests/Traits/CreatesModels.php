<?php

namespace Tests\Traits;

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
}
