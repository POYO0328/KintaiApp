@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/address.css') }}">

<div class="address-form__content">
    <h1 class="address-form__heading">住所の変更</h1>

    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div style="background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('purchase.address.submit', ['item_id' => $item_id]) }}" class="address-form" enctype="multipart/form-data">
        @csrf

        @php
            $sessionAddress = session('purchase_address');
            $user = Auth::user();
        @endphp

        <div class="form__group">
            <div class="form__group-title">
                <label for="postal_code" class="form__label--item">郵便番号</label>
            </div>
            <div class="form__input--text">
                <input type="text" name="postal_code" id="postal_code"
                    value="{{ old('postal_code', $sessionAddress['postal_code'] ?? $user->postal_code) }}"
                    >
            </div>
        </div>

        <div class="form__group">
            <div class="form__group-title">
                <label for="address" class="form__label--item">住所</label>
            </div>
            <div class="form__input--text">
                <input type="text" name="address" id="address"
                    value="{{ old('address', $sessionAddress['address'] ?? $user->address) }}"
                    >
            </div>
        </div>

        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">建物名</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="text" name="building"
                        value="{{ old('building', $sessionAddress['building'] ?? $user->building) }}">
                </div>
            </div>
        </div>


        <div class="form__button">
            <button type="submit" class="form__button-submit">次へ（確認画面）</button>
        </div>
    </form>
</div>
@endsection
