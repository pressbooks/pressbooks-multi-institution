<?php

namespace PressbooksMultiInstitution\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $institution_id
 * @property bool $manager
 */
class InstitutionUser extends Model
{
    public $timestamps = false;

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'institution_id' => 'integer',
        'manager' => 'boolean',
    ];

    protected $guarded = [];

    protected $table = 'institutions_users';

    public function user(): BelongsTo
    {
        // TODO: does this work?
        return $this->belongsTo('WP_User', 'user_id');
    }

    public function scopeSearchAndOrder($query, $request)
    {
        $search = $request['s'] ?? '';
        $builder = $query->where('name', 'like', "%{$search}%")
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
}
