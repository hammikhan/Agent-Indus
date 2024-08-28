<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiOffer extends Model
{
    use HasFactory;

    public function setFinaldataAttribute($value)
    {
        $this->attributes['finaldata'] = json_encode($value);
    }

    // Accessor to automatically decode JSON finaldata when retrieving
    public function getFinaldataAttribute($value)
    {
        return json_decode($value, true);
    }
    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }
}
