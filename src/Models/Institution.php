<?php

namespace PressbooksMultiInstitution\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    protected $guarded = [];

    public function domains(): HasMany
    {
        return $this->hasMany(EmailDomain::class);
    }

    public function managers(): HasMany
    {
        return $this->hasMany(Manager::class, 'institution_id', 'id')
            ->where('manager', 1);
    }

    public function scopeSearchAndOrder($query, $request)
    {
        $search = $request['s'] ?? '';
        return $query->where('name', 'like', "%{$search}%")
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
            })
            ->orderBy($request['orderby'] ?? 'name', $request['order'] ?? 'asc');
    }

    public function getEmailDomainsAttribute(): string
    {
        return $this->render('domains', ['domains' => $this->domains->pluck('domain')]);
    }

    public function getInstitutionalManagersAttribute(): string
    {
        return app()
            ->db
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
        return app()->Blade->render("PressbooksMultiInstitution::institutions.{$view}", $data);
    }
}
