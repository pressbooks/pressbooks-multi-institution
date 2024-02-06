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
        $this->managers()->delete();

        $this->managers()->createMany($managers);

        return $this;
    }

    public function users(): HasMany
    {
        return $this
            ->hasMany(InstitutionUser::class, 'institution_id', 'id');
    }

    public function managers(): HasMany
    {
        return $this->users()->where('manager', true);
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function scopeSearchAndOrder($query, $request)
    {
        $search = $request['s'] ?? '';
        $builder = $query->where('name', 'like', "%{$search}%")
            ->orWhere('book_limit', 'like', "%{$search}%")
            ->orWhere('user_limit', 'like', "%{$search}%")
            ->orWhereHas('domains', function ($query) use ($search) {
                $query->where('domain', 'like', "%{$search}%");
            })
            ->orWhereHas('managers', function ($query) use ($search) {
                $query->join('users', 'institutions_users.user_id', '=', 'users.ID')
                    ->where('users.user_login', 'like', "%{$search}%")
                    ->orWhere('users.display_name', 'like', "%{$search}%")
                    ->orWhere('users.user_email', 'like', "%{$search}%");
            });

        if(!isset($request['orderby']) && !isset($request['order'])) {
            return $builder;
        }

        // only order by the fields that are present in the table
        if(!in_array($request['orderby'], array_keys($this->casts))) {
            return $builder;
        }

        return $builder->orderBy($request['orderby'] ?? 'name', $request['order'] === 'asc' ? 'asc' : 'desc');
    }

    public function getEmailDomainsAttribute(): string
    {
        // TODO: rethink this
        return $this->render('domains', ['domains' => $this->domains->pluck('domain')]);
    }

    public function getInstitutionalManagersAttribute(): string
    {
        // TODO: rethink this
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
