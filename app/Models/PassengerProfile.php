<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassengerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'type',
        'title',
        'firstName',
        'lastName',
        'gender',
        'dob',
        'region',
        'phone',
        'identity',
        'passportNumber',
        'passportExpiry',
        'identityNumber',
        'issueCountry'
    ];
}
