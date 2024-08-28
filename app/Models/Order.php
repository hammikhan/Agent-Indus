<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }
    public function agency()
    {
        return $this->belongsTo(TravelAgency::class, 'agency_id');
    }
}
