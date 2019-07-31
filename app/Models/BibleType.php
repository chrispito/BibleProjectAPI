<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BibleType extends Model
{
    
    public $timestamps = false;

    /**
     * Get all related Bible.
     */
    public function bible()
    {
        return $this->bolongsTo(Bible::class);
    }
}
