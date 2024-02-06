<?php

namespace PressbooksMultiInstitution\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $blog_id
 * @property int $institution_id
 */
class Book extends Model
{
    public $timestamps = false;

    protected $casts = [
        'id' => 'integer',
        'blog_id' => 'integer',
        'institution_id' => 'integer',
    ];

    protected $guarded = [];

    protected $table = 'institutions_blogs';

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
