<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelAgency extends Model
{
    use HasFactory,SoftDeletes;

    public function creditLimits()
    {
        return $this->morphMany(CreditLimit::class, 'creditable');
    }
    public function pricingGroup()
    {
        return $this->belongsTo(PricingGroup::class, 'pricing_group_id');
    }
}
