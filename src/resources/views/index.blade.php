@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="attendance__alert">
  {{-- メッセージ機能 --}}
</div>

@php
    $keyword = request('keyword');
    $mylistUrl = $keyword ? url('/?page=mylist&keyword=' . urlencode($keyword)) : url('/?page=mylist');
@endphp

<div class="top__nav">
  <a href="{{ url('/') }}" class="top__nav-link {{ request('page') !== 'mylist' ? 'active' : '' }}">おすすめ</a>
  <a href="{{ $mylistUrl }}" class="top__nav-link {{ request('page') === 'mylist' ? 'active' : '' }}">マイリスト</a>
</div>

<hr style="border-color: #ccc; margin: 10px 0;">

<div class="product__list">
  @forelse ($items as $item)
    <div class="product__item">
      <a href="{{ url('item/' . $item->id) }}">
        <div class="item-image-wrapper">
          <img src="{{ asset(ltrim($item->image_path, '/')) }}" alt="{{ $item->item_name }}" class="product__image">
          @if($item->is_sold)
            <div class="sold-triangle"></div>
            <div class="sold-text">SOLD</div>
          @endif
        </div>
      </a>
    <div class="product__name">{{ $item->item_name }}</div>
  </div>
  @empty
    <p>該当する商品が見つかりませんでした。</p>
  @endforelse
</div>
@endsection
