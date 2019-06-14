<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BibleType extends Model
{
    
    
    /**
     * Get all related Bible.
     */
    public function article()
    {
        return $this->bolongsTo(Bible::class);
    }
}
