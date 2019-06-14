<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bible extends Model
{
      /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'book_nr',
        'chapter_nr',
        'verse_nr',
        'verse'
    ];

    /**
     * Get the related Bible type
     */
    public function type()
    {
        return $this->hasOne(BibleType::class);
    }

}
