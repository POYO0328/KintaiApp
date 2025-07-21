<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use App\Models\Like;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $page = $request->query('page');
        $items = collect();
    
        if ($page === 'mylist' && $user) {
            $likedItemIds = Like::where('user_id', $user->id)->pluck('items_id');
            $items = Item::whereIn('id', $likedItemIds)->get();
        } elseif ($user) {
            $items = Item::where('user_id', '!=', $user->id)->get();
        } else {
            $items = Item::all();
        }
    
        return view('index', compact('items'));
    }
    
}
