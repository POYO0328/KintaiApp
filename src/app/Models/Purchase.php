<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    
    protected $fillable = [
    'user_id',
    'item_id',
    'payment_method',
    'shipping_postal_code',
    'shipping_address',
    'shipping_building',
    ];

}
