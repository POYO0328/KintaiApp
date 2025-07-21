<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Http\Requests\ExhibitionRequest;

class ItemController extends Controller
{  
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $page = $request->input('page');
        $user = Auth::user();
    
        $query = Item::query();
    
        // ログインユーザーの出品を除外
        if ($user) {
            $query->where('user_id', '!=', $user->id);
        }
    
        // キーワード検索（部分一致）
        if (!empty($keyword)) {
            $query->where('item_name', 'like', '%' . $keyword . '%');
        }
    
        // マイリスト（いいねした商品）
        if ($page === 'mylist') {
            if ($user) {
                $query->whereHas('likes', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            } else {
                // 未ログインの場合は何も取得しないように
                $query->whereRaw('0 = 1'); // 常に false の条件
            }
        }
    
        $items = $query->get();
    
        return view('index', compact('items', 'keyword', 'page'));
    }
    


    public function show($id)
    {
        $item = Item::findOrFail($id);

        // コメント取得
        $comments = Comment::where('items_id', $id)->with('user')->latest()->get();

        // いいね数
        $likeCount = Like::where('items_id', $id)->count();

        // いいね状態
        $user = Auth::user();
        $isLiked = false;
        if ($user) {
            $isLiked = Like::where('user_id', $user->id)
                        ->where('items_id', $id)
                        ->exists();
        }

        // 複数カテゴリ対応（"1,3,5" など）
        $categoryIds = explode(',', $item->category_id);  // カンマで分割
        $categories = \App\Models\Category::whereIn('id', $categoryIds)->pluck('category_name');  // 名前だけ取得

        return view('item.show', compact('item', 'comments', 'likeCount', 'isLiked', 'categories'));
    }


    public function store(ExhibitionRequest $request)
    {
        $validated = $request->validated();

        // 画像処理
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $validated['image_path'] = 'storage/' . $path;
        }

        if (is_array($validated['category_id'])) {
            $validated['category_id'] = implode(',', $validated['category_id']);
        }

        // 必要に応じてカラム名を修正してください
        $itemData = [
            'item_name'        => $validated['item_name'],
            'description'      => $validated['description'],
            'image_path'       => $validated['image_path'] ?? null,
            'brand'            => $validated['brand'],
            'category_id'      => $validated['category_id'],
            'condition'        => $validated['condition'],
            'price'            => $validated['price'],
            'user_id'          => auth()->id(),
        ];

        Item::create($itemData);

        return redirect('/mypage')->with('success', '商品を出品しました！');
    }
}
