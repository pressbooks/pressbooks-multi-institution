<?php

namespace PressbooksMultiInstitution\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Manager extends Model
{
    protected $table = 'institutions_users';
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo('WP_User', 'user_id');
    }
}
