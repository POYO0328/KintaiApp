<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function rules()
    {
        return [
            'postal_code'   => ['required', 'regex:/^\d{3}-\d{4}$/', 'max:8'],
            'address'       => ['required', 'string', 'max:255'],
            'building'      => ['nullable', 'max:255'],
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'postal_code.required' => '郵便番号を入力してください。',
            'postal_code.regex' => '郵便番号はハイフン含めて000―0000の形式で半角で入力してください。',
            'postal_code.max' => '郵便番号はハイフン含めて８文字以内で入力してください。',
            'address.required' => '住所を入力してください。',
            'address.string' => '正しく住所を入力してください。',
            'address.max' => '住所は255文字以内で入力してください。',
            'building.max' => '建物名は255文字以内で入力してください。',
        ];
    }
}
