@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/item.css') }}">

<div class="item-container">
    <!-- 左：商品画像 -->
    <div class="item-image">
        <div class="item-image-wrapper">
            <img src="{{ asset(ltrim($item->image_path, '/')) }}" alt="{{ $item->item_name }}">
                @if($item->is_sold)
                <div class="sold-triangle"></div>
                <div class="sold-text">SOLD</div>
                @endif
        </div>

    </div>

    <!-- 右：商品詳細 -->
    <div class="item-info">
        <h2 class="item-name">{{ $item->item_name }}</h2>
        <p class="brand-name">{{ $item->brand }}</p>
        <p class="price">¥{{ number_format($item->price) }} <span class="tax-included">（税込）</span></p>

        <div class="icons">
        @auth
            <form action="{{ route('like.toggle', ['item_id' => $item->id]) }}" method="POST">
                @csrf
                <button type="submit" class="icon" style="background: none; border: none; font-size: 24px; cursor: pointer;">
                    {{ $isLiked ? '★' : '☆' }}
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="icon" style="text-decoration: none; font-size: 24px;">
                ☆
            </a>
        @endauth
            <span class="comment-icon">💬</span>
        </div>
        <div class="icons">
            <span class="like-count">{{ $likeCount }}</span>
            <span class="comment-count">{{ $comments->count() }}</span>
        </div>

        @if(!$item->is_sold)
            @auth
                <form action="{{ route('purchase.show', ['item_id' => $item->id]) }}" method="get">
                    <button class="purchase-btn">購入手続きへ</button>
                </form>
            @else
                <a href="{{ route('login') }}">
                    <button class="purchase-btn">購入手続きへ</button>
                </a>
            @endauth
        @else
            <button class="purchase-btn" disabled style="background-color: gray; cursor: not-allowed;">
                売り切れ
            </button>
        @endif

        <div class="section">
            <div class="section-title">商品説明</div>
            <p>カラー：グレー</p> {{-- ※今後、動的に色を表示できるようにする --}}
            <p>新品<br>商品の状態は良好です。傷もありません。</p>
            <p>購入後、即発送いたします。</p>
        </div>

        <div class="section">
            <div class="section-title">商品の情報</div>
            <p>
                カテゴリー：
                @foreach ($categories as $category)
                    <span class="tag">{{ $category }}</span>{{ !$loop->last ? '  ' : '' }}
                @endforeach
            </p>
            <p class="condition">商品の状態：{{ $item-> condition }}</p>
        </div>

        <div class="section">
            <div class="section-title">コメント ({{ $comments->count() }})</div>
            @foreach ($comments as $comment)
                <div class="comment">
                    <div class="avatar">
                        @if(isset($comment->user) && $comment->user->profile_image_path)
                            <img src="{{ asset($comment->user->profile_image_path) }}" alt="ユーザー画像" class="avatar-img">
                        @else
                            <img src="{{ asset('images/default-avatar.png') }}" alt="デフォルト画像" class="avatar-img">
                        @endif
                    </div>
                    <div class="comment-body">
                        <div class="comment-user">{{ $comment->user->name ?? 'ゲスト' }}</div>
                        <div class="comment-text">{{ $comment->comment }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="section">
            @if ($errors->any())
                <div style="background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="section-comment-title">商品へのコメント</div>
            @auth
                <form action="{{ route('comment.store', ['item' => $item->id]) }}" method="POST">
                    @csrf
                    <textarea name="comment" class="comment-box" placeholder="コメントを入力してください" {{ $item->is_sold ? 'disabled' : '' }}></textarea>
                    <button type="submit" class="comment-submit" {{ $item->is_sold ? 'disabled style=background-color:gray;cursor:not-allowed;' : '' }}>
                        コメントを送信する
                    </button>
                </form>
                @if($item->is_sold)
                    <p style="color: red; margin-top: 5px;">※売り切れのためコメント投稿できません</p>
                @endif
            @else
                <a href="{{ route('login') }}">
                    <button class="comment-submit">ログインしてコメント</button>
                </a>
            @endauth
        </div>

    </div>
</div>
@endsection
