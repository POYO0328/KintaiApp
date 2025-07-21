@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">

<div class="purchase-wrapper">
    <div class="purchase-left">
        <img src="{{ asset($item->image_path ?? 'images/default.png') }}" alt="商品画像" class="item-image">
        <h2>{{ $item->item_name }}</h2>
        <p>¥ {{ number_format($item->price) }}</p>

        <hr>

        <h4>支払い方法</h4>
        <select name="payment_method_left" id="payment_method_left" required>
            <option disabled selected>選択してください</option>
            <option value="コンビニ支払い">コンビニ支払い</option>
            <option value="カード支払い">カード支払い</option>
        </select>

        <hr>

        <div class="shipping-info">
        @if ($errors->any())
            <div style="background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
            <h4>配送先</h4>
            <p>〒 {{ $postalCode ?? '---' }}</p>
            <p>{{ $address ?? '---' }}</p>
            <p>{{ $building ?? '' }}</p>
            <a href="{{ route('purchase.address', ['item_id' => $item->id]) }}">変更する</a>
        </div>

    </div>

    <div class="purchase-right">
    <form action="{{ route('purchase.complete', ['item_id' => $item->id]) }}" method="post">
    @csrf
    <input type="hidden" name="payment_method" id="payment_method">
    <input type="hidden" name="shipping_postal_code" value="{{ $postalCode }}">
    <input type="hidden" name="shipping_address" value="{{ $address }}">
    <input type="hidden" name="shipping_building" value="{{ $building }}">

    <table>
        <tr>
            <td>商品代金</td>
            <td>¥ {{ number_format($item->price) }}</td>
        </tr>
        <tr>
            <td>支払い方法</td>
            <td id="payment_method_display">未選択</td>
        </tr>
    </table>

        <button type="submit" class="purchase-btn">購入する</button>
    </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const leftSelect = document.getElementById('payment_method_left');
        const hiddenInput = document.getElementById('payment_method');
        const displayText = document.getElementById('payment_method_display');

        leftSelect.addEventListener('change', function () {
            const selectedValue = this.value;

            // hiddenフィールドに値をセット（フォーム送信用）
            hiddenInput.value = selectedValue;

            // 表示用のテキストも更新
            displayText.textContent = selectedValue;
        });
    });
</script>
@endpush