<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingGroup extends Model
{
    use HasFactory;
    protected $table = 'pricing_group';
    protected $guarded = ['id'];

    public function pricingEngineTravelAgents()
    {
        return $this->hasMany(PricingEngineTravelAgent::class, 'pricing_group_id');
    }
}
