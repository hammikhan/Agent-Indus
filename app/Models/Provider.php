<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'identifier',
        'namespace',
        'vendor_id',
        'balance',
        'status',
        'exclude_airlines'
    ];
    public function airlineDiscount()
    {
        return $this->hasMany(AirlineDiscount::class, 'provider_id');
    }
}
