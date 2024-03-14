<?php

namespace PressbooksMultiInstitution\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function scopeNotManagers(Builder $query): Builder
    {
        return $query->where('manager', false);
    }

    public function scopeManagers(Builder $query): Builder
    {
        return $query->where('manager', true);
    }

    public function scopeByInstitution(Builder $query, int $institution_id): Builder
    {
        return $query->where('institution_id', $institution_id);
    }

    public function scopeIsManager(Builder $query, int $user_id): Builder
    {
        return $query->managers()->where('user_id', $user_id);
    }
}
