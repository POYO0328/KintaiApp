<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'postal_code'   => ['required', 'regex:/^\d{3}-\d{4}$/', 'max:8'],
            'address'       => ['required', 'string', 'max:255'],
            'building'      => ['nullable', 'max:255'],
            'profile_image' => ['nullable', 'string', 'max:2048'],
            'profile_image' => ['nullable', 'file', 'mimes:jpeg,png'],
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'name.required' => 'お名前お名前を入力してください。',
            'name.string' => '正しくお名前を入力してください。',
            'name.max' => 'お名前は255文字以内で入力してください。',
            'postal_code.required' => '郵便番号を入力してください。',
            'postal_code.regex' => '郵便番号はハイフン含めて000―0000の形式で半角で入力してください。',
            'postal_code.max' => '郵便番号はハイフン含めて８文字以内で入力してください。',
            'address.required' => '住所を入力してください。',
            'address.string' => '正しく住所を入力してください。',
            'address.max' => '住所は255文字以内で入力してください。',
            'building.max' => '建物名は255文字以内で入力してください。',
            'profile_image.mimes' => 'プロフィール画像はJPEGまたはPNG形式のファイルを選択してください。',
            'profile_image.file' => '正しいファイルをアップロードしてください。',
        ];
    }
}
