<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'items_id',
        'comment',
    ];

    // ユーザーとのリレーション（任意）
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
