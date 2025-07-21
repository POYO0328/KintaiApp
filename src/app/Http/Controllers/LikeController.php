<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggle($item_id)
    {
        $user = Auth::user();
    
        $like = Like::where('user_id', $user->id)
                    ->where('items_id', $item_id)
                    ->first();
    
        if ($like) {
            // すでにいいね済み → 取り消し（削除）
            $like->delete();
        } else {
            // いいねしていない → 新規登録
            Like::create([
                'user_id' => $user->id,
                'items_id' => $item_id,
            ]);
        }
    
        return back();
    }
}
