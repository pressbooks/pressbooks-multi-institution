<?php

namespace PressbooksMultiInstitution\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

use function PressbooksMultiInstitution\Support\is_network_unlimited;

/**
 * @property int $id
 * @property string $name
 * @property int|null $book_limit
 * @property boolean $allow_institutional_managers
 * @property boolean $buy_in
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Institution extends Model
{
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'book_limit' => 'integer',
        'allow_institutional_managers' => 'boolean',
        'buy_in' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $guarded = [];

    public function domains(): HasMany
    {
        return $this->hasMany(EmailDomain::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(InstitutionUser::class, 'institution_id', 'id');
    }

    public function managers(): HasMany
    {
        return $this->users()->where('manager', true);
    }

    public function books(): HasMany
    {
        return $this->hasMany(InstitutionBook::class);
    }

    public function updateDomains(array $domains): self
    {
        $this->domains()->delete();

        $this->domains()->createMany($domains);

        return $this;
    }

    public function syncManagers(array $ids): self
    {
        $current = $this->users()->pluck('manager', 'user_id')->all();

        $managers = array_keys(array_filter($current));

        $detach = array_diff($managers, $ids);

        $this->managers()->whereIn('user_id', $detach)->update([
            'manager' => false,
        ]);

        $users = array_keys($current);

        foreach ($ids as $id) {
            if (in_array($id, $users)) {
                $this->users()->where('user_id', $id)->update([
                    'manager' => true,
                ]);

                continue;
            }

            $this->users()->create([
                'user_id' => $id,
                'manager' => true,
            ]);
        }

        apply_filters('pb_institutional_after_save', $ids, $detach);

        return $this;
    }

    public function allowsInstitutionalManagers(): bool
    {
        if (is_network_unlimited()) {
            return true;
        }

        if ($this->buy_in) {
            return true;
        }

        if ($this->allow_institutional_managers) {
            return true;
        }

        return false;
    }

    public function scopeSearchAndOrder($query, $request)
    {
        $search = $request['s'] ?? '';
        $builder = $query->where('name', 'like', "%{$search}%")
            ->orWhere('book_limit', 'like', "%{$search}%")
            ->orWhereHas('domains', function ($query) use ($search) {
                $query->where('domain', 'like', "%{$search}%");
            })
            ->orWhereHas('managers', function ($query) use ($search) {
                $query->join('users', 'institutions_users.user_id', '=', 'users.ID')
                    ->where('users.user_login', 'like', "%{$search}%")
                    ->orWhere('users.display_name', 'like', "%{$search}%")
                    ->orWhere('users.user_email', 'like', "%{$search}%");
            });

        if (!isset($request['orderby']) && !isset($request['order'])) {
            return $builder;
        }

        // only order by the fields that are present in the table
        if (!in_array($request['orderby'], array_keys($this->casts))) {
            return $builder;
        }

        return $builder->orderBy($request['orderby'] ?? 'name', $request['order'] === 'asc' ? 'asc' : 'desc');
    }
}
