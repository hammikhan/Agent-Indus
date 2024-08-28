<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'type', 'data', 'status'];

    // Mutator to automatically encode data as JSON before saving
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }

    // Accessor to automatically decrypt and decode JSON data when retrieving
    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }
}
