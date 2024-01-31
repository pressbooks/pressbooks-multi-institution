<?php

namespace PressbooksMultiInstitution\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property string $domain
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class EmailDomain extends Model
{
    protected $casts = [
        'id' => 'integer',
        'institution_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $guarded = [];

    protected $table = 'institutions_email_domains';

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
