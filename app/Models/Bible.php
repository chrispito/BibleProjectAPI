<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Illuminate\Support\Facades\Log;

class Bible extends Model
{
    use Searchable;

    public $timestamps = false;

    public $asYouType = true;

    protected $fillable = [
        'book_nr',
        'verse_id',
        'chapter_nr',
        'verse_nr',
        'verse',
        'verse_for_search'
    ];

    public function type()
    {
        return $this->hasOne(BibleType::class);
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();
        Log::info($array);

        return $array;
    }

    // public function getScoutKey()
    // {
    //     return $this->verse_id;
    // }

}
