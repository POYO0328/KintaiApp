<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'price',
        'brand',
        'condition',
        'description',
        'image_path',
        'user_id',
        'category_id',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function getIsSoldAttribute()
    {
        return $this->purchases()->exists(); // 購入レコードがあれば sold と判定
    }

    public function likes()
    {
        return $this->hasMany(\App\Models\Like::class, 'items_id');
    }

    // public function categories()
    // {
    //     return $this->belongsToMany(Category::class, 'category_item', 'item_id', 'category_id');
    // }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
