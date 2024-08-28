<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'account_title',
        'branch_code',
        'account_no',
        'iban',
        'status',
    ];
}
