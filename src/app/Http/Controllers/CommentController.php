<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest; // ← 追加
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(CommentRequest $request, $item_id) // ← 型を変更
    {
        // バリデーション済みデータを取得（オプション）
        $validated = $request->validated();

        Comment::create([
            'user_id' => Auth::id(),
            'items_id' => $item_id,
            'comment' => $validated['comment'], // 安全なバリデーション済みデータ
        ]);

        return redirect()->back()->with('success', 'コメントを投稿しました。');
    }
}
