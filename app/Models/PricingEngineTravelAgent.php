<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingEngineTravelAgent extends Model
{
    use HasFactory;

    protected $table = 'ta_pricing_engine';
    protected $guarded = ['id'];
    public static $rulePurpose = [
        1 => 'Mark Up',
        2 => 'Discount',
        3 => 'Route Mark Up',
        4 => 'Route Discount'
    ];

    public static $status = [
        1 => 'Active',
        0 => 'Inactive'
    ];

    public static $rulePurposeCast = [];
    public static $statusCast = [];

    // Initialize the static properties directly
    static function initializeStaticProperties() {
        self::$rulePurposeCast = array_flip(self::$rulePurpose);
        self::$statusCast = array_flip(self::$status);
    }

    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }

    public function api()
    {
        return $this->belongsTo(Setting::class, 'api_id', 'id');
    }
    public function pricingGroup()
    {
        return $this->belongsTo(PricingGroup::class, 'pricing_group_id');
    }
}

// Initialize the static properties
PricingEngineTravelAgent::initializeStaticProperties();
