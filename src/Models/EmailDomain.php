<?php

namespace PressbooksMultiInstitution\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailDomain extends Model
{
    protected $table = 'institutions_email_domains';

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
