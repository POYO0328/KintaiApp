<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Purchase;

class MypageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $page = $request->query('page', 'sell'); // デフォルトは "sell"

        if ($page === 'buy') {
            // purchases テーブル経由で購入した item を取得
            $items = Item::whereIn('id', function ($query) use ($user) {
                $query->select('item_id')
                    ->from('purchases')
                    ->where('user_id', $user->id);
            })->get();
        } else {
            // 出品商品（user_idが一致）
            $items = Item::where('user_id', $user->id)->get();
        }

        return view('mypage.index', compact('user', 'items', 'page'));
    }
}
