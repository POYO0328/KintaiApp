@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/sell.css') }}">

<div class="sell-container">
    <h2 class="title">商品の出品</h2>

    <form action="{{ route('item.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($errors->any())
            <div style="background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-group">
            <label for="image">商品画像</label><br>

            <label for="image" class="custom-file-upload">
                画像を選択
            </label>

            <input type="file" name="image" id="image" onchange="previewImage(event)">
            <div id="file-name" style="margin-top: 5px; color: #555;"></div>
            <div id="image-preview" style="margin-top: 10px;"></div>
        </div>

        <div class="form-subtitle-group">
            <label for="image" class="form-subtitle-label">商品の詳細</label>
        </div>
        <hr class="hr-gray">

        <div class="form-group">
            <label for="category_id">カテゴリー</label>
            <div class="category-buttons">
                @foreach($categories as $category)
                    <input type="checkbox" name="category_id[]" value="{{ $category->id }}" id="category_{{ $category->id }}" class="category-checkbox">
                    <label class="category-button" for="category_{{ $category->id }}">
                        {{ $category->category_name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <label for="condition">商品の状態</label>
            <select name="condition" id="condition">
                <option value="">選択してください</option>
                <option value="良好">良好</option>
                <option value="目立った傷や汚れなし">目立った傷や汚れなし</option>
                <option value="やや傷や汚れあり">やや傷や汚れあり</option>
                <option value="状態が悪い">状態が悪い</option>
            </select>
        </div>


        <div class="form-subtitle-group">
            <label for="image" class="form-subtitle-label">商品名と説明</label>
        </div>
        <hr class="hr-gray">

        <div class="form-group">
            <label for="item_name">商品名</label>
            <input type="text" name="item_name" id="item_name" >
        </div>

        <div class="form-group">
            <label for="brand">ブランド名</label>
            <input type="text" name="brand" id="brand" >
        </div>

        <div class="form-group">
            <label for="description">商品の説明</label>
            <textarea name="description" id="description" rows="5"></textarea>
        </div>

        <div class="form-group">
            <label for="price">販売価格</label>
            <div class="price-input">
                <span class="yen">¥</span>
                <input type="number" name="price" id="price" >
            </div>
        </div>

        <div class="form-buttons">
            <button type="submit" class="submit-btn">出品する</button>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
function previewImage(event) {
    const preview = document.getElementById('image-preview');
    const fileNameDisplay = document.getElementById('file-name');

    preview.innerHTML = ''; // 画像プレビューをクリア
    fileNameDisplay.textContent = ''; // ファイル名表示をクリア

    const file = event.target.files[0];
    if (file && file.type.startsWith('image/')) {
        // ファイル名を表示
        fileNameDisplay.textContent = `選択されたファイル: ${file.name}`;

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '200px';
            img.style.maxHeight = '200px';
            img.style.border = '1px solid #ccc';
            img.style.borderRadius = '8px';
            img.style.marginTop = '10px';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endpush
