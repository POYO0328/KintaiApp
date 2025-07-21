<?php
// app/Http/Controllers/SellController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class SellController extends Controller
{
    public function showForm()
    {
        $categories = Category::all(); // DBからカテゴリ一覧取得
        return view('sell.form', compact('categories'));
    }
}
