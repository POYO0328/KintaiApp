<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        // Fortifyが使うユーザー作成処理を実行
        $user = app(CreatesNewUsers::class)->create($request->all());

        // ログイン処理
        Auth::login($user);

        // 登録後に /mypage/profile にリダイレクト
        return redirect('/mypage/profile');
    }
}
