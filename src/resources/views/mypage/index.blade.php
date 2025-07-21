@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

@section('content')
<div class="mypage">
    <div class="profile">
        <img src="{{ asset($user->profile_image_path) }}" alt="ユーザー画像" class="avatar">
        <div class="username">{{ $user->name }}</div>
        <a href="mypage/profile" class="edit-button">プロフィールを編集</a>
    </div>

    <div class="tabs">
        <a href="{{ url('/mypage?page=sell') }}" class="{{ $page === 'sell' ? 'active' : '' }}">出品した商品</a>
        <a href="{{ url('/mypage?page=buy') }}" class="{{ $page === 'buy' ? 'active' : '' }}">購入した商品</a>
    </div>

    <div class="items-grid">
        @if(isset($items) && $items->count())
            @foreach ($items as $item)
                <div class="item-card">
                    <div class="item-image-wrapper">
                        <img src="{{ asset($item->image_path) }}" alt="{{ $item->item_name }}">
                        @if($item->is_sold)
                        <div class="sold-triangle"></div>
                        <div class="sold-text">SOLD</div>
                        @endif
                    </div>
                    <p>{{ $item->item_name }}</p>
                </div>
            @endforeach
        @else
            <p>出品した商品はありません。</p>
        @endif
    </div>
</div>
@endsection
@push('scripts')
    @if (session('payment_method') === 'カード支払い' || session('payment_method') === 'コンビニ支払い')
        <script src="https://js.stripe.com/v3/"></script>
        <script>
    document.addEventListener('DOMContentLoaded', async function () {
        const stripeKey = "{{ config('services.stripe.key') }}";
        console.log('Stripeキー:', stripeKey);
        const stripeInstance = Stripe(stripeKey); // ← 変数名を変更

        const response = await fetch("/stripe/create-checkout-session", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                item_id: "{{ session('purchased_item_id') }}"
            })
        });

        const session = await response.json();

        const result = await stripeInstance.redirectToCheckout({
            sessionId: session.id
        });

        if (result.error) {
            alert("Stripe起動エラー: " + result.error.message);
        }
    });
</script>

    @endif
@endpush

