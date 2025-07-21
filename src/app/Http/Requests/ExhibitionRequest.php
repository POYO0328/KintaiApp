<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'item_name' => ['required', 'string'],
            'description' => ['required', 'string', 'max:255'],
            'brand' => ['max:255'],
            'image' => ['required', 'file', 'mimes:jpeg,png'],
            'category_id' => ['required', 'array'],
            'category_id.*' => ['exists:categories,id'],
            'condition' => ['required'],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'item_name.required' => '商品名は必須です。',
            'description.required' => '商品説明は必須です。',
            'description.max' => '商品説明は255文字以内で入力してください。',
            'image.required' => '商品画像は必須です。',
            'image.mimes' => '画像はjpegまたはpng形式でアップロードしてください。',
            'image.max' => '画像は2MB以内でアップロードしてください。',
            'category_id.required' => 'カテゴリは必須です。',
            'condition.required' => '商品の状態は必須です。',
            'price.required' => '価格は必須です。',
            'price.numeric' => '価格は数字で入力してください。',
            'price.min' => '価格は0以上で入力してください。',
        ];
    }
}
