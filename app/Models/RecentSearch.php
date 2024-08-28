<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecentSearch extends Model
{
    use HasFactory;

    protected $fillable = ['travelagent_id','origin','destination','data'];
    
    // Mutator to automatically encode data as JSON before saving
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }

    // Accessor to automatically decode JSON data when retrieving
    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }
}
