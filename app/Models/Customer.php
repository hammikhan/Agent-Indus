<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['travelagent_id','name','email','phone','country','address','passenger_email','passenger_country','passenger_phone'];

    public function passengerProfiles()
    {
        return $this->hasMany(PassengerProfile::class);
    }
}
