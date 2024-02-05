<?php

namespace PressbooksMultiInstitution\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'name' => 'string',
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

    public function managers(): HasMany
    {
        return $this->hasMany(Manager::class, 'institution_id', 'id')
            ->where('manager', 1);
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function scopeSearchAndOrder(Builder $query, $request)
    {
        $search = $request['s'] ?? null;

        $direction = $request['order'] ?? 'asc';
        $orderBy = $request['orderby'] ?? null;

        if (! array_key_exists($orderBy, $this->casts)) {
            $orderBy = 'name';
        }

        return $query
            ->when($search, function (Builder $query, $search) {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('book_limit', 'like', "%{$search}%")
                    ->orWhere('user_limit', 'like', "%{$search}%")
                    ->orWhereRelation('domains', 'domain', 'like', "%{$search}%")
                    ->orWhereHas('managers', function ($query) use ($search) {
                        $query->join('users', 'institutions_users.user_id', '=', 'users.ID')
                            ->where('users.user_login', 'like', "%{$search}%")
                            ->orWhere('users.display_name', 'like', "%{$search}%")
                            ->orWhere('users.user_email', 'like', "%{$search}%");
                    });
            })
            ->when($orderBy && $direction, function (Builder $query) use ($orderBy, $direction) {
                $query->orderBy($orderBy, $direction);
            });
    }

    public function getEmailDomainsAttribute(): string
    {
        return $this->render('domains', ['domains' => $this->domains->pluck('domain')]);
    }

    public function getInstitutionalManagersAttribute(): string
    {
        return app('db')
            ->table('institutions_users')
            ->join('users', 'institutions_users.user_id', '=', 'users.ID')
            ->where('institution_id', $this->id)
            ->orderBy('users.display_name')
            ->where('manager', 1)->get()->map(function ($manager) {
                return $this->render('managers', ['manager' => $manager]);
            })->implode('');
    }

    private function render(string $view, array $data = []): string
    {
        return app('Blade')->render("PressbooksMultiInstitution::institutions.{$view}", $data);
    }
}
