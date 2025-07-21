<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    public function rules()
    {
        return [
            'payment_method' => ['required', 'in:コンビニ支払い,カード支払い'],
            'shipping_postal_code' => ['required', 'string'],
            'shipping_address' => ['required', 'string'],
            'shipping_building' => ['nullable', 'string'],
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'payment_method.required' => '支払い方法は必ず指定してください。',
            'payment_method.in' => '支払い方法は「コンビニ支払い」か「カード支払い」のどちらかを選んでください。',
            'shipping_postal_code.required' => '郵便番号は必ず入力してください。',
            'shipping_address.required' => '住所は必ず入力してください。',

        ];
    }


}
