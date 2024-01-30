<?php

namespace PressbooksMultiInstitution\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int|null $book_limit
 * @property int|null $user_limit
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Institution extends Model
{
    protected $casts = [
        'id' => 'integer',
        'book_limit' => 'integer',
        'user_limit' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $guarded = [];

    public function domains(): HasMany
    {
        return $this->hasMany(EmailDomain::class);
    }

    public function updateDomains(array $domains): self
    {
        $this->domains()->delete();

        $this->domains()->createMany($domains);

        return $this;
    }

    public function updateManagers(array $managers): self
    {
        global $wpdb;

        $table = "{$wpdb->base_prefix}institutions_users";

        $wpdb->delete($table, [
            'institution_id' => $this->id,
        ]);

        foreach ($managers as $manager) {
            $wpdb->insert("{$wpdb->base_prefix}institutions_users", [
                'institution_id' => $this->id,
                'user_id' => (int) $manager,
                'manager' => true,
            ]);
        }

        return $this;
    }
}
