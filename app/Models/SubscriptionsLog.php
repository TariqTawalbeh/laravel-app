<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionsLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'subscription_number',
        'status',
        'transaction_date'
    ];
}
